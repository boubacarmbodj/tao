<?php
/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2013 (original work) Open Assessment Techonologies SA (under the project TAO-PRODUCT);
 *               
 * 
 */

/**
 * The ParserFactory provides some methods to build the QTI_Data objects from an
 * element.
 * SimpleXML is used as source to build the model.
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package taoItems
 * @subpackage models_classes_QTI
 */
class taoQTI_models_classes_QTI_ParserFactory
{

    /**
     * Build a QTI_Item from a SimpleXMLElement (the root tag of this element is
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  SimpleXMLElement data
     * @return taoQTI_models_classes_QTI_Item
     * @see http://www.imsglobal.org/question/qti_v2p0/imsqti_infov2p0.html#element10010
     */
    public static function buildItem( SimpleXMLElement $data)
    {
        $returnValue = null;

		//check on the root tag.

		if(isset($data['identifier'])){
			$itemId = (string) $data['identifier'];//might be an issue if the identifier given is no good, e.g. twice the same value...
		}
		
		common_Logger::i('Started parsing of QTI item'.(isset($itemId) ? ' '.$itemId : ''), array('TAOITEMS'));
			
		//retrieve the item attributes
		$options = array();
		foreach($data->attributes() as $key => $value){
			$options[$key] = (string) $value;
		}
		unset($options['identifier']);

		//create the item instance
		$myItem = new taoQTI_models_classes_QTI_Item($itemId, $options);

		//get the stylesheets
		$styleSheets = array();
		$styleSheetNodes = $data->xpath("*[name(.) = 'stylesheet']");
		foreach($styleSheetNodes as $styleSheetNode){
			$styleSheets[] = array(
       			'href' 	=> (string) $styleSheetNode['href'],		//mandaory field
       			'title' => (isset($styleSheetNode['title'])) ? (string) $styleSheetNode['title'] : '', 
       			'media'	=> (isset($styleSheetNode['media'])) ? (string) $styleSheetNode['media'] : 'screen',
       			'type'	=> (isset($styleSheetNode['type']))  ? (string) $styleSheetNode['type'] : 'text/css',
			);
		}
		$myItem->setStylesheets($styleSheets);

		// parse for objects
		$objectNodes = $data->xpath("//*[name(.) = 'object']");
		//name(./*[1]) = "responseIf"
		foreach($objectNodes as $objectNode){
			
			$object = self::buildObject($objectNode);
			if (!is_null($object))
				$myItem->addObject($object);
		}
		
		//parse the xml to find the interaction nodes
		$interactionNodes = $data->xpath("//*[contains(name(.), 'Interaction')]");
		foreach($interactionNodes as $interactionNode){
			//build an interaction instance by found node
			$interaction = self::buildInteraction($interactionNode);
			if(!is_null($interaction)){
				$myItem->addInteraction($interaction);
			}
		}

		//extract the item structure to separate the structural/style content to the item content
		$itemBodies = $data->xpath("*[name(.) = 'itemBody']"); // array with 1 or zero bodies
		if ($itemBodies === false) {
			$errors = libxml_get_errors();
			if (count($errors) > 0) {
				$error = array_shift($errors); 
				$errormsg = $error->message;
			} else {
				$errormsg = "without errormessage";
			}
			throw new taoQTI_models_classes_QTI_ParsingException('XML error('.$errormsg.') on itemBody read'.(isset($itemId) ? ' for item '.$itemId : ''));
		} 
		$itemData = '';
		foreach ($itemBodies as $itemBody) {
			foreach ($itemBody->children() as $child) {
				$itemData .= $child->asXml();
			}
		}
		
		if(!empty($itemData)){
			foreach($myItem->getInteractions() as $interaction){
				//map the interactions by an identified tag: {interaction.serial}
				$tag = $interaction->getType().'Interaction';
				$pattern = "/<{$tag}\b[^>]*>(.*?)<\/{$tag}>|(<{$tag}\b[^>]*\/>)/is";
				$itemData = preg_replace($pattern, "{{$interaction->getSerial()}}", $itemData, 1);
			}
			foreach($myItem->getObjects() as $object){
				$pattern = "/(<object\b[^>]*\/>)|<object\b[^>]*>(.*?)<\/object>/is";
				$itemData = preg_replace($pattern, "{{$object->getSerial()}}", $itemData, 1);
			}
			$myItem->setData($itemData);
		}

		//extract thee responses
		$responseNodes = $data->xpath("*[name(.) = 'responseDeclaration']");
		foreach($responseNodes as $responseNode){
			$response = self::buildResponse($responseNode);
			if(!is_null($response)){
				foreach($myItem->getInteractions() as $interaction){
					if($interaction->getOption('responseIdentifier') == $response->getIdentifier()){
						$interaction->setResponse($response);
						break;
					}
				}
			}
		}

		//extract outcome variables
		$outcomes = array();
		$outComeNodes = $data->xpath("*[name(.) = 'outcomeDeclaration']");
		foreach($outComeNodes as $outComeNode){
			$outcome = self::buildOutcome($outComeNode);
			if(!is_null($outcome)){
				$outcomes[] = $outcome;
			}
		}
		if(count($outcomes) > 0){
			$myItem->setOutcomes($outcomes);
		}
		
		//extract the response processing
		$rpNodes = $data->xpath("*[name(.) = 'responseProcessing']");
		if (count($rpNodes) == 0) {
			common_Logger::i('No responseProcessing found for QTI Item, setting empty custom', array('QTI', 'TAOITEMS'));
			$customrp = new taoQTI_models_classes_QTI_response_Custom(array(), '<responseProcessing/>');
			$myItem->setResponseProcessing($customrp);
		} else {
			$rpNode = array_shift($rpNodes);//the node should be alone
			$rProcessing = self::buildResponseProcessing($rpNode, $myItem);
			if(!is_null($rProcessing)){
				$myItem->setResponseProcessing($rProcessing);
			}
		}

		$returnValue = $myItem;

        return $returnValue;
    }

    /**
     * Build a QTI_Interaction from a SimpleXMLElement (the root tag of this
     * is an 'interaction' node)
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  SimpleXMLElement data
     * @return taoQTI_models_classes_QTI_Interaction
     * @see http://www.imsglobal.org/question/qti_v2p0/imsqti_infov2p0.html#element10247
     */
    public static function buildInteraction( SimpleXMLElement $data)
    {
        $returnValue = null;

		$options = array();
		foreach($data->attributes() as $key => $value){
			if($key == "matchGroup") {
				$options[$key] = explode(' ', $value);
			}
			else{
				$options[$key] = (string) $value;
			}
		}
		try{
			$type = str_replace('Interaction', '', $data->getName());
			$myInteraction = new taoQTI_models_classes_QTI_Interaction($type, null, $options);

			//build the interaction regarding it's type
			switch($type){

				case 'match':
					//extract simpleMatchSet choices
					$matchSetNodes = $data->xpath("*[name(.) = 'simpleMatchSet']");
					$count = 0;
					foreach($matchSetNodes as $matchSetNode){
						$choiceNodes = $matchSetNode->xpath("*[name(.) = 'simpleAssociableChoice']");
						$choices = array();
						foreach($choiceNodes as $choiceNode){
							$choice = self::buildChoice($choiceNode);
							if(!is_null($choice)){
								$myInteraction->addChoice($choice);
								$choices[] = $choice;
							}
						}
						//and create group with the sets
						$group = new taoQTI_models_classes_QTI_Group();
						$group->setType($matchSetNode->getName());
						$group->setChoices($choices);
						$myInteraction->addGroup($group);
						
						if(++$count==2){
							break;
						}
					}
					break;

				case 'gapMatch':
					//create choices with the gapText nodes
					$choiceNodes = $data->xpath("*[name(.)='gapText']");
					$choices = array();
					foreach($choiceNodes as $choiceNode){
						$choice = self::buildChoice($choiceNode);
						if(!is_null($choice)){
							$myInteraction->addChoice($choice);
							$choices[$choice->getIdentifier()] = $choice;
						}
					}
					//create a group with each gap node (this a particular use of the group)
					$gapNodes = $data->xpath(".//*[name(.)='gap']");
					foreach($gapNodes as $gapNode){
						$group = new taoQTI_models_classes_QTI_Group((string) $gapNode['identifier']);
						$group->setType($gapNode->getName());
						if(isset($gapNode['matchGroup'])){
							$matchChoice = array();
							$group->setOption('matchGroup', explode(' ', (string) $gapNode['matchGroup']));
							foreach($group->getOption('matchGroup') as $choiceId){
								if(array_key_exists($choiceId, $choices)){
									$matchChoice[] = $choices[$choiceId];
								}
							}
							$group->setChoices($matchChoice);
						}
						else{
							$group->setChoices($choices);
						}

						$myInteraction->addGroup($group);
					}
					break;
				case 'graphicGapMatch':
					//extract the media object tag
					$objectNodes = $data->xpath("*[name(.)='object']");
					foreach($objectNodes as $objectNode){
						$objectData = array();
						foreach($objectNode->attributes() as $key => $value){
							$objectData[$key] = (string) $value;
						}

						if(count($objectNode->children()) > 0){
							//get the node xml content
							$pattern = array("/^<{$objectNode->getName()}([^>]*)?>/i", "/<\/{$data->getName()}([^>]*)?>$/i");
							$content = preg_replace($pattern, "", trim($objectNode->asXML()));
							if(empty($content)){
								$content = (string) $objectNode;
							}
							$objectData['_alt'] = $content;
						}
						else{
							$objectData['_alt'] = (string) $objectNode;
						}
						$myInteraction->setObject($objectData);
					}

					//create choices with the gapImg nodes
					$choiceNodes = $data->xpath("*[name(.)='gapImg']");
					$choices = array();
					foreach($choiceNodes as $choiceNode){
						$choice = self::buildChoice($choiceNode);
						if(!is_null($choice)){
							$myInteraction->addChoice($choice);
							$choices[$choice->getIdentifier()] = $choice;
						}
					}
					//create a group with each gap node (this a particular use of the group)
					$gapNodes = $data->xpath(".//*[name(.)='associableHotspot']");
					foreach($gapNodes as $gapNode){
						$group = new taoQTI_models_classes_QTI_Group((string) $gapNode['identifier']);
						$group->setType($gapNode->getName());
						if(isset($gapNode['matchGroup'])){
							$matchChoice = array();
							$group->setOption('matchGroup', explode(' ', (string) $gapNode['matchGroup']));
							foreach($group->getOption('matchGroup') as $choiceId){
								if(array_key_exists($choiceId, $choices)){
									$matchChoice[] = $choices[$choiceId];
								}
							}
							$group->setChoices($matchChoice);
						}
						else{
							$group->setChoices($choices);
						}
						if(isset($gapNode['matchMax'])){
							$group->setOption('matchMax', (int) $gapNode['matchMax']);
						}
						if(isset($gapNode['shape'])){
							$group->setOption('shape', (string) $gapNode['shape']);
						}
						if(isset($gapNode['coords'])){
							$group->setOption('coords', (string) $gapNode['coords']);
						}
						$myInteraction->addGroup($group);
					}
					break;
				case 'hotspot':
				case 'selectPoint':
				case 'graphicOrder':
				case 'graphicAssociate':
					//extract the media object tag
					$objectNodes = $data->xpath("*[name(.)='object']");
					foreach($objectNodes as $objectNode){
						$objectData = array();
						foreach($objectNode->attributes() as $key => $value){
							$objectData[$key] = (string) $value;
						}

						if(count($objectNode->children()) > 0){
							//get the node xml content
							$pattern = array("/^<{$objectNode->getName()}([^>]*)?>/i", "/<\/{$data->getName()}([^>]*)?>$/i");
							$content = preg_replace($pattern, "", trim($objectNode->asXML()));
							if(empty($content)){
								$content = (string) $objectNode;
							}
							$objectData['_alt'] = $content;
						}
						else{
							$objectData['_alt'] = (string) $objectNode;
						}
						$myInteraction->setObject($objectData);
					}
				default :
					//parse, extract and build the choice nodes contained in the interaction
					$interactionData = simplexml_load_string($data->asXML());
					$exp= "*[contains(name(.),'Choice')] | *[name(.)='associableHotspot'] | //*[name(.)='hottext']";
					$choiceNodes = $interactionData->xpath($exp);
					foreach($choiceNodes as $choiceNode){
						$choice = self::buildChoice($choiceNode);
						if(!is_null($choice)){
							$myInteraction->addChoice($choice);
						}
					}
					break;
			}

			//extract the interaction structure to separate the structural/style content to the interaction content
			$interactionNodes = $data->children();

			//get the interaction data
			$interactionData = '';
			foreach($interactionNodes as $interactionNode){
				$interactionData .= $interactionNode->asXml();
			}
			if(!empty($interactionData)){

				switch($type){

					case 'match':{
						foreach($myInteraction->getGroups() as $group){
							//map the group by a identified tag: {group-serial}
							$tag = $group->getType();
							$pattern = "/(<{$tag}\b[^>]*>(.*?)<\/{$tag}>)|(<{$tag}\b[^>]*\/>)/is";
							$interactionData = preg_replace($pattern, "{{$group->getSerial()}}", $interactionData, 1);
						}

						break;
					}
					case 'gapMatch':
					case 'graphicGapMatch':{
						foreach($myInteraction->getGroups() as $group){
							//map the group by a identified tag: {group-serial}
							$tag = $group->getType();
							$pattern = "/(<{$tag}\b[^>]*>(.*?)<\/{$tag}>)|(<{$tag}\b[^>]*\/>)/is";
							$interactionData = preg_replace($pattern, "{{$group->getSerial()}}", $interactionData, 1);
						}
					}
					case 'hotspot':
					case 'selectPoint':
					case 'graphicOrder':
					case 'graphicAssociate':{
							
						$pattern = "/(<object\b[^>]*>(.*?)<\/object>)|(<object\b[^>]*\/>)/is";
						$interactionData = preg_replace($pattern, "", $interactionData);
					}
					default:{
						foreach($myInteraction->getChoices() as $choice){
							//map the choices by a identified tag: {choice-serial}
							$tag = $choice->getType();
							$pattern = "/(<{$tag}\b[^>]*[^\/]>(.*?)<\/{$tag}>)|(<{$tag}\b[^>]*\/>)/is";
							$interactionData = preg_replace($pattern, "{{$choice->getSerial()}}", $interactionData, 1);
						}
					}
				}

				//extract the prompt tag to the attribute
				$promptData = '';
				$promptNodes = $data->xpath("*[name(.) = 'prompt']");
				foreach($promptNodes as $promptNode){
					if(count($promptNode->children()) > 0){
						$promptData .= preg_replace(array("/^<prompt([^>]*)?>/i", "/<\/prompt([^>]*)?>$/i"), "", trim($promptNode->asXML()));
					}
					else{
						$promptData .= (string) $promptNode;
					}
				}
				$myInteraction->setPrompt($promptData);

				//remove the prompt from the data string
				$pattern = "/(<prompt\b[^>]*>(.*?)<\/prompt>)|(<prompt\b[^>]*\/>)/is";
				$interactionData = preg_replace($pattern, "", $interactionData);
					
				//set the data string
				$myInteraction->setData($interactionData);
			}

			$returnValue = $myInteraction;
		}
		catch(InvalidArgumentException $iae){
			throw new taoQTI_models_classes_QTI_ParsingException($iae);
		}

        return $returnValue;
    }

    /**
     * Build a QTI_Choice from a SimpleXMLElement (the root tag of this element
     * an 'choice' node)
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  SimpleXMLElement data
     * @return taoQTI_models_classes_QTI_Choice
     * @see http://www.imsglobal.org/question/qti_v2p0/imsqti_infov2p0.html#element10254
     */
    public static function buildChoice( SimpleXMLElement $data)
    {
        $returnValue = null;

		$options = array();
		foreach($data->attributes() as $key => $value){
			if($key == "matchGroup") {
				$options[$key] = explode(' ', $value);
			}
			else{
				$options[$key] = (string) $value;
			}
		}
		unset($options['identifier']);

		if(!isset($data['identifier'])){
			throw new taoQTI_models_classes_QTI_ParsingException("No identifier found for the choice {$data->getName()}");
		}

		$myChoice = new taoQTI_models_classes_QTI_Choice((string) $data['identifier'], $options);
		$myChoice->setType($data->getName());

		if($myChoice->getType() == 'gapImg'){

			//extract the media object tag
			$objectNodes = $data->xpath("*[name(.)='object']");
			foreach($objectNodes as $objectNode){
				$objectData = array();
				foreach($objectNode->attributes() as $key => $value){
					$objectData[$key] = (string) $value;
				}

				if(count($objectNode->children()) > 0){
					//get the node xml content
					$pattern = array("/^<{$objectNode->getName()}([^>]*)?>/i", "/<\/{$data->getName()}([^>]*)?>$/i");
					$content = preg_replace($pattern, "", trim($objectNode->asXML()));
					if(empty($content)){
						$content = (string) $objectNode;
					}
					$objectData['_alt'] = $content;
				}
				else{
					$objectData['_alt'] = (string) $objectNode;
				}
				$myChoice->setObject($objectData);
			}
		}
		if(count($data->children()) > 0){
			//get the node xml content
			$pattern = array("/^<{$data->getName()}([^>]*)?>/i", "/<\/{$data->getName()}([^>]*)?>$/i");
			$content = preg_replace($pattern, "", trim($data->asXML()));
			if(empty($content)){
				$content = (string) $data;
			}
			$myChoice->setData($content);
		}
		else{
			$myChoice->setData((string) $data);
		}

		$returnValue = $myChoice;

        return $returnValue;
    }

    /**
     * Short description of method buildResponse
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  SimpleXMLElement data
     * @return taoQTI_models_classes_QTI_Response
     * @see http://www.imsglobal.org/question/qti_v2p0/imsqti_infov2p0.html#element10074
     */
    public static function buildResponse( SimpleXMLElement $data)
    {
        $returnValue = null;

		$options = array();
		foreach($data->attributes() as $key => $value){
			$options[$key] = (string) $value;
		}
		unset($options['identifier']);

		if(!isset($data['identifier'])){
			throw new taoQTI_models_classes_QTI_ParsingException("No identifier found for {$data->getName()}");
		}

		$myResponse = new taoQTI_models_classes_QTI_Response((string) $data['identifier'], $options);
		$myResponse->setType($data->getName());

		//set the correct responses
		$correctResponseNodes = $data->xpath("*[name(.) = 'correctResponse']");
		$responses = array();
		foreach($correctResponseNodes as $correctResponseNode){
			foreach($correctResponseNode->value as $value){
				$responses[] = (string) $value;
			}
			break;
		}
		$myResponse->setCorrectResponses($responses);

		//set the mapping if defined
		$mappingNodes = $data->xpath("*[name(.) = 'mapping']");
		foreach($mappingNodes as $mappingNode){

			if(isset($mappingNode['defaultValue'])){
				$myResponse->setMappingDefaultValue(floatval((string) $mappingNode['defaultValue']));
			}
			$mappingOptions = array();
			foreach($mappingNode->attributes() as $key => $value){
				if($key != 'defaultValue'){
					$mappingOptions[$key] = (string) $value;
				}
			}
			$myResponse->setOption('mapping', $mappingOptions);

			$mapping = array();
			foreach($mappingNode->mapEntry as $mapEntry){
				$mapping[(string) $mapEntry['mapKey']] = (string) $mapEntry['mappedValue'];
			}
			$myResponse->setMapping($mapping);

			break;
		}

		//set the areaMapping if defined
		$mappingNodes = $data->xpath("*[name(.) = 'areaMapping']");
		foreach($mappingNodes as $mappingNode){

			if(isset($mappingNode['defaultValue'])){
				$myResponse->setMappingDefaultValue(floatval((string) $mappingNode['defaultValue']));
			}
			$mappingOptions = array();
			foreach($mappingNode->attributes() as $key => $value){
				if($key != 'defaultValue'){
					$mappingOptions[$key] = (string) $value;
				}
			}
			$myResponse->setOption('areaMapping', $mappingOptions);

			$mapping = array();
			foreach($mappingNode->areaMapEntry as $mapEntry){
				$mappingAttributes = array();
				foreach($mapEntry->attributes() as $key => $value){
					$mappingAttributes[(string) $key] = (string) $value;
				}
				$mapping[] = $mappingAttributes;
			}
			$myResponse->setMapping($mapping, 'area');

			break;
		}

		$returnValue = $myResponse;

        return $returnValue;
    }

    /**
     * Short description of method buildOutcome
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  SimpleXMLElement data
     * @return taoQTI_models_classes_QTI_Outcome
     */
    public static function buildOutcome( SimpleXMLElement $data)
    {
        $returnValue = null;

		$options = array();
		foreach($data->attributes() as $key => $value){
			$options[$key] = (string) $value;
		}
		unset($options['identifier']);

		if(!isset($data['identifier'])){
			throw new taoQTI_models_classes_QTI_ParsingException("No identifier found for an {$data->getName()}");
		}

		$outCome = new taoQTI_models_classes_QTI_Outcome((string) $data['identifier'], $options);
		if(isset($outcome->defaultValue)){
			$outCome->setDefaultValue((string) $outcome->defaultValue->value);
		}

		$returnValue = $outCome;

        return $returnValue;
    }

    /**
     * Short description of method buildTemplateResponseProcessing
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  SimpleXMLElement data
     * @return taoQTI_models_classes_QTI_response_ResponseProcessing
     */
    public static function buildTemplateResponseProcessing( SimpleXMLElement $data)
    {
        $returnValue = null;

		if(isset($data['template']) && count($data->children()) == 0) {
			$templateUri = (string) $data['template'];

			$returnValue = new taoQTI_models_classes_QTI_response_Template($templateUri);

		} elseif (count($data->children()) == 1) {
			$patternCorrectIMS		= 'responseCondition [count(./*) = 2 ] [name(./*[1]) = "responseIf" ] [count(./responseIf/*) = 2 ] [name(./responseIf/*[1]) = "match" ] [name(./responseIf/match/*[1]) = "variable" ] [name(./responseIf/match/*[2]) = "correct" ] [name(./responseIf/*[2]) = "setOutcomeValue" ] [name(./responseIf/setOutcomeValue/*[1]) = "baseValue" ] [name(./*[2]) = "responseElse" ] [count(./responseElse/*) = 1 ] [name(./responseElse/*[1]) = "setOutcomeValue" ] [name(./responseElse/setOutcomeValue/*[1]) = "baseValue"]';
			$patternMappingIMS		= 'responseCondition [count(./*) = 2] [name(./*[1]) = "responseIf"] [count(./responseIf/*) = 2] [name(./responseIf/*[1]) = "isNull"] [name(./responseIf/isNull/*[1]) = "variable"] [name(./responseIf/*[2]) = "setOutcomeValue"] [name(./responseIf/setOutcomeValue/*[1]) = "variable"] [name(./*[2]) = "responseElse"] [count(./responseElse/*) = 1] [name(./responseElse/*[1]) = "setOutcomeValue"] [name(./responseElse/setOutcomeValue/*[1]) = "mapResponse"]';
			$patternMappingPointIMS	= 'responseCondition [count(./*) = 2] [name(./*[1]) = "responseIf"] [count(./responseIf/*) = 2] [name(./responseIf/*[1]) = "isNull"] [name(./responseIf/isNull/*[1]) = "variable"] [name(./responseIf/*[2]) = "setOutcomeValue"] [name(./responseIf/setOutcomeValue/*[1]) = "variable"] [name(./*[2]) = "responseElse"] [count(./responseElse/*) = 1] [name(./responseElse/*[1]) = "setOutcomeValue"] [name(./responseElse/setOutcomeValue/*[1]) = "mapResponsePoint"]';
			if (count($data->xpath($patternCorrectIMS)) == 1) {
				$returnValue = new taoQTI_models_classes_QTI_response_Template(taoQTI_models_classes_QTI_response_Template::MATCH_CORRECT);
			} elseif (count($data->xpath($patternMappingIMS)) == 1) {
				$returnValue = new taoQTI_models_classes_QTI_response_Template(taoQTI_models_classes_QTI_response_Template::MAP_RESPONSE);
			} elseif (count($data->xpath($patternMappingPointIMS)) == 1) {
				$returnValue = new taoQTI_models_classes_QTI_response_Template(taoQTI_models_classes_QTI_response_Template::MAP_RESPONSE_POINT);
			} else {
				throw new taoQTI_models_classes_QTI_UnexpectedResponseProcessingException('not Template, wrong rule');
			}
		} else {
			throw new taoQTI_models_classes_QTI_UnexpectedResponseProcessingException('not Template');
		}

        return $returnValue;
    }

    /**
     * Short description of method buildResponseProcessing
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  SimpleXMLElement data
     * @param  Item item
     * @return taoQTI_models_classes_QTI_response_ResponseProcessing
     */
    public static function buildResponseProcessing( SimpleXMLElement $data,  taoQTI_models_classes_QTI_Item $item)
    {
        $returnValue = null;

        // try template
        try {
        	$returnValue = self::buildTemplateResponseProcessing($data);
	        try {
	    		$returnValue = taoQTI_models_classes_QTI_response_TemplatesDriven::takeOverFrom($returnValue, $item);
	       		common_Logger::d('Processing is Template converted to TemplateDriven', array('TAOITEMS', 'QTI'));
	        } catch (taoQTI_models_classes_QTI_response_TakeoverFailedException $e) {
	        	common_Logger::d('Processing is Template', array('TAOITEMS', 'QTI'));
	        }
        } catch (taoQTI_models_classes_QTI_UnexpectedResponseProcessingException $e) {
		}
        
		//try templatedriven
        if (is_null($returnValue)) {
	        try {
	        	$returnValue = self::buildTemplatedrivenResponse($data, $item->getInteractions());
	        	common_Logger::d('Processing is TemplateDriven', array('TAOITEMS', 'QTI'));
	        } catch (taoQTI_models_classes_QTI_UnexpectedResponseProcessingException $e) {
			}
        }
        
        //try composite
        if (is_null($returnValue)) {
	        try {
	        	$returnValue = self::buildCompositeResponseProcessing($data, $item);
	        	common_Logger::d('Processing is Composite', array('TAOITEMS', 'QTI'));
	        } catch (taoQTI_models_classes_QTI_UnexpectedResponseProcessingException $e) {
	        	common_Logger::d($e->getMessage());
			}
        	
        }
        /*
        // convert template to composite
        if (!is_null($returnValue)) {
	        try {
	    		$returnValue = taoQTI_models_classes_QTI_response_Composite::takeOverFrom($returnValue, $item);
	        } catch (Exception $e) {
	        	common_Logger::w('Could not be converted to Composite', array('TAOITEMS', 'QTI'));
			}
        }
        */
	    // build custom
        if (is_null($returnValue))
	        try {
	        	$returnValue = self::buildCustomResponseProcessing($data);
        		common_Logger::d('ResponseProcessing is custom');
	        } catch (taoQTI_models_classes_QTI_UnexpectedResponseProcessingException $e) {
	        	// not a Template
	        	common_Logger::e('custom response processing failed, should never happen', array('TAOITEMS', 'QTI'));
	        }
	        
        if (is_null($returnValue)) {
        	common_Logger::d('failled to determin ResponseProcessing');
        }

        return $returnValue;
    }

    /**
     * Short description of method buildCompositeResponseProcessing
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  SimpleXMLElement data
     * @param  Item item
     * @return taoQTI_models_classes_QTI_response_ResponseProcessing
     */
    public static function buildCompositeResponseProcessing( SimpleXMLElement $data,  taoQTI_models_classes_QTI_Item $item)
    {
        $returnValue = null;

        // STRONGLY simplified summation detection
        $patternCorrectTAO	= '/responseCondition [count(./*) = 1 ] [name(./*[1]) = "responseIf" ] [count(./responseIf/*) = 2 ] [name(./responseIf/*[1]) = "match" ] [name(./responseIf/match/*[1]) = "variable" ] [name(./responseIf/match/*[2]) = "correct" ] [name(./responseIf/*[2]) = "setOutcomeValue" ] [count(./responseIf/setOutcomeValue/*) = 1 ] [name(./responseIf/setOutcomeValue/*[1]) = "baseValue"]';        
        $patternMapTAO		= '/responseCondition [count(./*) = 1 ] [name(./*[1]) = "responseIf" ] [count(./responseIf/*) = 2 ] [name(./responseIf/*[1]) = "not" ] [count(./responseIf/not/*) = 1 ] [name(./responseIf/not/*[1]) = "isNull" ] [count(./responseIf/not/isNull/*) = 1 ] [name(./responseIf/not/isNull/*[1]) = "variable" ] [name(./responseIf/*[2]) = "setOutcomeValue" ] [count(./responseIf/setOutcomeValue/*) = 1 ] [name(./responseIf/setOutcomeValue/*[1]) = "mapResponse"]';       
        $patternMapPointTAO	= '/responseCondition [count(./*) = 1 ] [name(./*[1]) = "responseIf" ] [count(./responseIf/*) = 2 ] [name(./responseIf/*[1]) = "not" ] [count(./responseIf/not/*) = 1 ] [name(./responseIf/not/*[1]) = "isNull" ] [count(./responseIf/not/isNull/*) = 1 ] [name(./responseIf/not/isNull/*[1]) = "variable" ] [name(./responseIf/*[2]) = "setOutcomeValue" ] [count(./responseIf/setOutcomeValue/*) = 1 ] [name(./responseIf/setOutcomeValue/*[1]) = "mapResponsePoint"]';       
        $patternNoneTAO		= '/responseCondition [count(./*) = 1 ] [name(./*[1]) = "responseIf" ] [count(./responseIf/*) = 2 ] [name(./responseIf/*[1]) = "isNull" ] [count(./responseIf/isNull/*) = 1 ] [name(./responseIf/isNull/*[1]) = "variable" ] [name(./responseIf/*[2]) = "setOutcomeValue" ] [count(./responseIf/setOutcomeValue/*) = 1 ] [name(./responseIf/setOutcomeValue/*[1]) = "baseValue"]';       
        $possibleSummation	= '/setOutcomeValue [count(./*) = 1 ] [name(./*[1]) = "sum" ]';
        
		$irps = array();
		$composition = null;
		foreach ($data as $responseRule) {
			if (!is_null($composition))
				throw new taoQTI_models_classes_QTI_UnexpectedResponseProcessingException('Not composite, rules after composition');
			
			$subtree = new SimpleXMLElement($responseRule->asXML());

			if (count($subtree->xpath($patternCorrectTAO)) > 0 ) {
				$responseIdentifier = (string) $subtree->responseIf->match->variable[0]['identifier'];
				$irps[$responseIdentifier] = array(
					'class'		=> 'MatchCorrectTemplate',
					'outcome'	=> (string) $subtree->responseIf->setOutcomeValue[0]['identifier']
				);
			} elseif (count($subtree->xpath($patternMapTAO)) > 0 ) {
				$responseIdentifier = (string) $subtree->responseIf->not->isNull->variable[0]['identifier'];
				$irps[$responseIdentifier] = array(
					'class'		=> 'MapResponseTemplate',
					'outcome'	=> (string) $subtree->responseIf->setOutcomeValue[0]['identifier']
				);
			} elseif (count($subtree->xpath($patternMapPointTAO)) > 0 ) {
				$responseIdentifier = (string) $subtree->responseIf->not->isNull->variable[0]['identifier'];
				$irps[$responseIdentifier] = array(
					'class'		=> 'MapResponsePointTemplate',
					'outcome'	=> (string) $subtree->responseIf->setOutcomeValue[0]['identifier']
				);
			} elseif (count($subtree->xpath($patternNoneTAO)) > 0 ) {
				$responseIdentifier = (string) $subtree->responseIf->isNull->variable[0]['identifier'];
				$irps[$responseIdentifier] = array(
					'class'		=> 'None',
					'outcome'	=> (string) $subtree->responseIf->setOutcomeValue[0]['identifier'],
					'default'	=> (string) $subtree->responseIf->setOutcomeValue[0]->baseValue[0]
				);
			} elseif (count($subtree->xpath($possibleSummation)) > 0 ) {
				$composition = 'Summation';
				$outcomesUsed = array();
				foreach ($subtree->xpath('/setOutcomeValue/sum/variable') as $var) {
					$outcomesUsed[] = (string) $var[0]['identifier'];
				}
			} else {
				throw new taoQTI_models_classes_QTI_UnexpectedResponseProcessingException('Not composite, unknown rule');
			}
		}
		
		if (is_null($composition)) {
			throw new taoQTI_models_classes_QTI_UnexpectedResponseProcessingException('Not composit, Composition rule missing');
		}
		
		$responses = array();
		foreach ($item->getInteractions() as $interaction) {
			$responses[$interaction->getResponse()->getIdentifier()] = $interaction->getResponse();
		}
		
		if (count(array_diff(array_keys($irps), array_keys($responses))) > 0) {
			throw new taoQTI_models_classes_QTI_UnexpectedResponseProcessingException('Not composit, no responses for rules: '.implode(',',array_diff(array_keys($irps), array_keys($responses))));
		}
		if (count(array_diff(array_keys($responses), array_keys($irps))) > 0) {
			common_Logger::w('Some responses have no processing');
			throw new taoQTI_models_classes_QTI_UnexpectedResponseProcessingException('Not composit, no support for unmatched variables yet');
		}
		
		//assuming sum is correct
		
		$compositonRP = new taoQTI_models_classes_QTI_response_Summation($item);
        foreach ($responses as $id => $response) {
        	$outcome = null;
        	foreach ($item->getOutcomes() as $possibleOutcome) {
        		if ($possibleOutcome->getIdentifier() == $irps[$id]['outcome']) {
        				$outcome = $possibleOutcome;
        				break;
        		}
        	}
        	if (is_null($outcome)) {
        		$compositonRP->destroy();
				throw new taoQTI_models_classes_QTI_ParsingException('Undeclared Outcome in ResponseProcessing');
        	}
        	$classname = 'taoQTI_models_classes_QTI_response_interactionResponseProcessing_'.$irps[$id]['class'];
        	$irp = new $classname($response, $outcome);
        	if ($irp instanceof taoQTI_models_classes_QTI_response_interactionResponseProcessing_none && isset($irps[$id]['default'])) {
        		$irp->setDefaultValue($irps[$id]['default']);
        	}
        	$compositonRP->add($irp);
        }
		$returnValue = $compositonRP;
        
        return $returnValue;
    }

    /**
     * Short description of method buildCustomResponseProcessing
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  SimpleXMLElement data
     * @return taoQTI_models_classes_QTI_response_ResponseProcessing
     */
    public static function buildCustomResponseProcessing( SimpleXMLElement $data)
    {
        $returnValue = null;

		// Parse to find the different response rules
		$responseRules = array ();

		foreach ($data->children() as $child) {
			$responseRules[] = taoQTI_models_classes_QTI_response_ResponseRuleParserFactory::buildResponseRule($child);
		}
		//@todocheck responseCustom 
		$returnValue = new taoQTI_models_classes_QTI_response_Custom($responseRules, $data->asXml());

        return $returnValue;
    }

    /**
     * Enables you to build the QTI_Resources from a manifest xml data node
     * Content Packaging 1.1)
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  SimpleXMLElement source
     * @return array
     * @see http://www.imsglobal.org/question/qti_v2p0/imsqti_intgv2p0.html#section10003
     */
    public static function getResourcesFromManifest( SimpleXMLElement $source)
    {
        $returnValue = array();

		//check of the root tag
		if($source->getName() != 'manifest'){
			throw new Exception("incorrect manifest root tag");
		}
			
		$resourceNodes = $source->xpath("//*[name(.)='resource']");
		foreach($resourceNodes as $resourceNode){
			$type = (string) $resourceNode['type'];
			if(taoQTI_models_classes_QTI_Resource::isAllowed($type)){
					
				$id = (string) $resourceNode['identifier'];
				(isset($resourceNode['href'])) ? $href = (string) $resourceNode['href'] : $href = '';
					
				$auxFiles = array();
				$xmlFiles = array();
				foreach($resourceNode->file as $fileNode){
					$fileHref = (string) $fileNode['href'];
					if(preg_match("/\.xml$/", $fileHref)){
						if(empty($href)){
							$xmlFiles[] = $fileHref;
						}
					}
					else{
						$auxFiles[] = $fileHref;
					}
				}
					
				if(count($xmlFiles) == 1 && empty($href)){
					$href = $xmlFiles[0];
				}
				$resource = new taoQTI_models_classes_QTI_Resource($id, $href);
				$resource->setAuxiliaryFiles($auxFiles);
					
				$returnValue[] = $resource;
			}
		}

        return (array) $returnValue;
    }

    /**
     * Short description of method buildExpression
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  SimpleXMLElement data
     * @return taoQTI_models_classes_QTI_response_Rule
     */
    public static function buildExpression( SimpleXMLElement $data)
    {
        $returnValue = null;

		// The factory will create the right expression for us
		$expression = taoQTI_models_classes_QTI_expression_ExpressionParserFactory::build($data);
		
		$returnValue = $expression;

        return $returnValue;
    }

    /**
     * Short description of method buildTemplatedrivenResponse
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  SimpleXMLElement data
     * @param  array interactions
     * @return taoQTI_models_classes_QTI_response_ResponseProcessing
     */
    public static function buildTemplatedrivenResponse( SimpleXMLElement $data, $interactions)
    {
        $returnValue = null;
		
		$patternCorrectTAO = '/responseCondition [count(./*) = 1 ] [name(./*[1]) = "responseIf" ] [count(./responseIf/*) = 2 ] [name(./responseIf/*[1]) = "match" ] [name(./responseIf/match/*[1]) = "variable" ] [name(./responseIf/match/*[2]) = "correct" ] [name(./responseIf/*[2]) = "setOutcomeValue" ] [name(./responseIf/setOutcomeValue/*[1]) = "sum" ] [name(./responseIf/setOutcomeValue/sum/*[1]) = "variable" ] [name(./responseIf/setOutcomeValue/sum/*[2]) = "baseValue"]';
		$patternMappingTAO = '/responseCondition [count(./*) = 1] [name(./*[1]) = "responseIf"] [count(./responseIf/*) = 2] [name(./responseIf/*[1]) = "not"] [name(./responseIf/not/*[1]) = "isNull"] [name(./responseIf/not/isNull/*[1]) = "variable"] [name(./responseIf/*[2]) = "setOutcomeValue"] [name(./responseIf/setOutcomeValue/*[1]) = "sum"] [name(./responseIf/setOutcomeValue/sum/*[1]) = "variable"] [name(./responseIf/setOutcomeValue/sum/*[2]) = "mapResponse"]';
		$patternMappingPointTAO = '/responseCondition [count(./*) = 1] [name(./*[1]) = "responseIf"] [count(./responseIf/*) = 2] [name(./responseIf/*[1]) = "not"] [name(./responseIf/not/*[1]) = "isNull"] [name(./responseIf/not/isNull/*[1]) = "variable"] [name(./responseIf/*[2]) = "setOutcomeValue"] [name(./responseIf/setOutcomeValue/*[1]) = "sum"] [name(./responseIf/setOutcomeValue/sum/*[1]) = "variable"] [name(./responseIf/setOutcomeValue/sum/*[2]) = "mapResponsePoint"]';
        
		$rules = array();
		foreach ($data as $responseRule) {
			$subtree = new SimpleXMLElement($responseRule->asXML());

			if (count($subtree->xpath($patternCorrectTAO)) > 0 ) {
				$variable = $subtree->responseIf->match->variable;
				$responseIdentifier = (string) $variable[0]['identifier'];
				$rules[$responseIdentifier] = taoQTI_models_classes_QTI_response_Template::MATCH_CORRECT;
			} elseif (count($subtree->xpath($patternMappingTAO)) > 0 ) {
				$variable = $subtree->responseIf->not->isNull->variable;
				$responseIdentifier = (string) $variable[0]['identifier'];
				$rules[$responseIdentifier] = taoQTI_models_classes_QTI_response_Template::MAP_RESPONSE;
			} elseif (count($subtree->xpath($patternMappingPointTAO)) > 0 ) {
				$variable = $subtree->responseIf->not->isNull->variable;
				$responseIdentifier = (string) $variable[0]['identifier'];
				$rules[$responseIdentifier] = taoQTI_models_classes_QTI_response_Template::MAP_RESPONSE_POINT;
			} else {
				throw new taoQTI_models_classes_QTI_UnexpectedResponseProcessingException('Not template driven, unknown rule');
			}
		}
		
		$responseIdentifiers = array();
		foreach ($interactions as $interaction){
			$responseIdentifiers[] = $interaction->getResponse()->getIdentifier();
		}
		
		if (count(array_diff($responseIdentifiers, array_keys($rules))) > 0
			|| count(array_diff(array_keys($rules), $responseIdentifiers)) > 0) {
			throw new taoQTI_models_classes_QTI_UnexpectedResponseProcessingException('Not template driven, responseIdentifiers are '.implode(',',$responseIdentifiers).' while rules are '.implode(',',array_keys($rules)));
		}
		
        $templatesDrivenRP = new taoQTI_models_classes_QTI_response_TemplatesDriven();
        foreach ($interactions as $interaction){
			$pattern = $rules[$interaction->getResponse()->getIdentifier()];
			$templatesDrivenRP->setTemplate($interaction->getResponse(), $pattern);
		}
		$returnValue = $templatesDrivenRP;

        return $returnValue;
    }

    /**
     * Short description of method buildObject
     *
     * @access private
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  SimpleXMLElement data
     * @return taoQTI_models_classes_QTI_Object
     */
    private static function buildObject( SimpleXMLElement $data)
    {
        $returnValue = null;

        $returnValue = new taoQTI_models_classes_QTI_Object();
        foreach ($data->attributes() as $k => $v) {
        	$returnValue->setOption((string)$k, (string)$v);
        }

        return $returnValue;
    }

} /* end of class taoQTI_models_classes_QTI_ParserFactory */