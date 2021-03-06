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
 * The QTI_Item object represent the assessmentItem.
 * It's the main QTI object, it contains all the other objects and is the main
 * point
 * to render a complete item.
 *
 * @access public
 * @author Jehan Bihin, <jehan.bihin@tudor.lu>
 * @package taoItems
 * @see http://www.imsglobal.org/question/qti_v2p0/imsqti_infov2p0.html#section10042
 * @subpackage models_classes_QTI
 */
class taoQTI_models_classes_QTI_Item
    extends taoQTI_models_classes_QTI_Data
{
    // --- ASSOCIATIONS ---
    // generateAssociationEnd :     // generateAssociationEnd :

    // --- ATTRIBUTES ---

    /**
     * Item's interactions
     *
     * @access protected
     * @var array
     */
    protected $interactions = array();

    /**
     * Item's reponse processing
     *
     * @access protected
     * @var ResponseProcessing
     */
    protected $responseProcessing = null;

    /**
     * Item's outcomes
     *
     * @access protected
     * @var array
     */
    protected $outcomes = array();

    /**
     * Item's stylesheets
     *
     * @access protected
     * @var array
     */
    protected $stylesheets = array();

    /**
     * Short description of attribute objects
     *
     * @access public
     * @var array
     */
    public $objects = array();

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  string identifier
     * @param  array options
     * @return mixed
     */
    public function __construct($identifier = null, $options = array())
    {
        // section 127-0-1-1-2993bc96:12baebd89c3:-8000:000000000000264D begin

    	//override the tool options !
    	$options['toolName'] 	= PRODUCT_NAME;
    	$options['toolVersion'] = TAO_VERSION;

    	parent::__construct($identifier, $options);

        // section 127-0-1-1-2993bc96:12baebd89c3:-8000:000000000000264D end
    }

    /**
     * Short description of method getInteractions
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @return array
     */
    public function getInteractions()
    {
        $returnValue = array();

        // section 127-0-1-1--4be859a6:12a33452171:-8000:00000000000023D6 begin

        $returnValue = $this->interactions;

        // section 127-0-1-1--4be859a6:12a33452171:-8000:00000000000023D6 end

        return (array) $returnValue;
    }

    /**
     * Short description of method setInteractions
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  array interactions
     * @return mixed
     */
    public function setInteractions($interactions)
    {
        // section 127-0-1-1--4be859a6:12a33452171:-8000:00000000000023D8 begin

    	$this->interactions = array();
    	foreach($interactions as $interaction){
    		$this->addInteraction($interaction);
    	}

        // section 127-0-1-1--4be859a6:12a33452171:-8000:00000000000023D8 end
    }

    /**
     * Short description of method getInteraction
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  string serial
     * @return taoQTI_models_classes_QTI_Interaction
     */
    public function getInteraction($serial)
    {
        $returnValue = null;

        // section 127-0-1-1--4be859a6:12a33452171:-8000:00000000000023DB begin

        if(!empty($serial)){
	        if(array_key_exists($serial, $this->interactions)){
	        	$returnValue = $this->interactions[$serial];
	        }
        }

        // section 127-0-1-1--4be859a6:12a33452171:-8000:00000000000023DB end

        return $returnValue;
    }

    /**
     * Short description of method addInteraction
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  Interaction interaction
     * @return mixed
     */
    public function addInteraction( taoQTI_models_classes_QTI_Interaction $interaction)
    {
        // section 127-0-1-1--4be859a6:12a33452171:-8000:00000000000023DF begin

    	if(!is_null($interaction)){
    		$this->interactions[$interaction->getSerial()] = $interaction;

    		//allow resonseProcessing to adapt to new interaction
    		if (!is_null($this->getResponseProcessing()))
				$this->getResponseProcessing()->takeNoticeOfAddedInteraction($interaction, $this);
    	}


        // section 127-0-1-1--4be859a6:12a33452171:-8000:00000000000023DF end
    }

    /**
     * Short description of method removeInteraction
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  Interaction interaction
     * @return boolean
     */
    public function removeInteraction( taoQTI_models_classes_QTI_Interaction $interaction)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--398d1ef5:12acc40a46b:-8000:0000000000002538 begin

    	if(!is_null($interaction)){
    		if(isset($this->interactions[$interaction->getSerial()])){

    			//delete interaction response if set:
    			$this->getResponseProcessing()->takeNoticeOfRemovedInteraction($interaction, $this);

				//finally, delete the interaction:
    			$interaction->destroy();
    			unset($this->interactions[$interaction->getSerial()]);

    			//allow responseProcessing to do cleanup
    			$returnValue = true;
    		}
    	}

        // section 127-0-1-1--398d1ef5:12acc40a46b:-8000:0000000000002538 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method getResponseProcessing
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @return taoQTI_models_classes_QTI_response_ResponseProcessing
     */
    public function getResponseProcessing()
    {
        $returnValue = null;

        // section 127-0-1-1--398d1ef5:12acc40a46b:-8000:000000000000253B begin

        $returnValue = $this->responseProcessing;

        // section 127-0-1-1--398d1ef5:12acc40a46b:-8000:000000000000253B end

        return $returnValue;
    }

    /**
     * Short description of method setResponseProcessing
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  rprocessing
     * @return mixed
     */
    public function setResponseProcessing($rprocessing)
    {
        // section 127-0-1-1--398d1ef5:12acc40a46b:-8000:000000000000253E begin

    	$this->responseProcessing = $rprocessing;

        // section 127-0-1-1--398d1ef5:12acc40a46b:-8000:000000000000253E end
    }

    /**
     * Short description of method setOutcomes
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  array outcomes
     * @return mixed
     */
    public function setOutcomes($outcomes)
    {
        // section 127-0-1-1--a2bd9f7:12ae6efc8e9:-8000:00000000000025A4 begin

    	$this->outcomes = array();
    	foreach($outcomes as $outcome){
    		if( ! $outcome instanceof taoQTI_models_classes_QTI_Outcome){
    			throw new InvalidArgumentException("wrong entry in outcomes list");
    		}
    		$this->outcomes[$outcome->getSerial()] = $outcome;
    	}

        // section 127-0-1-1--a2bd9f7:12ae6efc8e9:-8000:00000000000025A4 end
    }

    /**
     * Short description of method getOutcomes
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @return array
     */
    public function getOutcomes()
    {
        $returnValue = array();

        // section 127-0-1-1--a2bd9f7:12ae6efc8e9:-8000:00000000000025A7 begin

        $returnValue = $this->outcomes;

        // section 127-0-1-1--a2bd9f7:12ae6efc8e9:-8000:00000000000025A7 end

        return (array) $returnValue;
    }

    /**
     * Short description of method getOutcome
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  string serial
     * @return taoQTI_models_classes_QTI_Outcome
     */
    public function getOutcome($serial)
    {
        $returnValue = null;

        // section 127-0-1-1--a2bd9f7:12ae6efc8e9:-8000:00000000000025A9 begin

     	if(!empty($serial)){
	        if(array_key_exists($serial, $this->outcomes)){
	        	$returnValue = $this->outcomes[$serial];
	        }
        }

        // section 127-0-1-1--a2bd9f7:12ae6efc8e9:-8000:00000000000025A9 end

        return $returnValue;
    }

    /**
     * Short description of method removeOutcome
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  Outcome outcome
     * @return boolean
     */
    public function removeOutcome( taoQTI_models_classes_QTI_Outcome $outcome)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--a2bd9f7:12ae6efc8e9:-8000:00000000000025AC begin

	    if(!is_null($outcome)){
    		if(isset($this->outcomes[$outcome->getSerial()])){
    			// this function will be called from destroy
    			// so no need to call $outcome->destroy();
    			unset($this->outcomes[$outcome->getSerial()]);
    			$returnValue = true;
    		}
    	} else {
    		common_Logger::w('Tried to remove null outcome');
    	}

    	if (!$returnValue) {
    		common_Logger::w('outcome not found '.$outcome->getSerial());
    	}
        // section 127-0-1-1--a2bd9f7:12ae6efc8e9:-8000:00000000000025AC end

        return (bool) $returnValue;
    }

    /**
     * Short description of method getStylesheets
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @return array
     */
    public function getStylesheets()
    {
        $returnValue = array();

        // section 127-0-1-1-8cf5183:12bce4ebee2:-8000:000000000000271E begin

        $returnValue = $this->stylesheets;

        // section 127-0-1-1-8cf5183:12bce4ebee2:-8000:000000000000271E end

        return (array) $returnValue;
    }

    /**
     * Short description of method setStylesheets
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  array stylesheets
     * @return mixed
     */
    public function setStylesheets($stylesheets)
    {
        // section 127-0-1-1-8cf5183:12bce4ebee2:-8000:0000000000002720 begin

        $this->stylesheets = array();
    	if(is_array($stylesheets)){
    		foreach($stylesheets as $stylesheet){
    			if(isset($stylesheet['href']) && isset($stylesheet['title'])){
    				$this->stylesheets[] = $stylesheet;
    			}
    		}
    	}

        // section 127-0-1-1-8cf5183:12bce4ebee2:-8000:0000000000002720 end
    }

    /**
     * Short description of method toXHTML
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @return string
     */
    public function toXHTML()
    {
        $returnValue = (string) '';

        // section 127-0-1-1--56c234f4:12a31c89cc3:-8000:0000000000002388 begin

        $template  = self::getTemplatePath() . '/xhtml.item.tpl.php';

    	//get the variables to use in the template
    	$variables = $this->extractVariables();
		
    	//these variables enables to get only the needed resources
		$variables['hasUpload'] 	= false;
		$variables['hasGraphics'] 	= false;
		$variables['hasSlider']		= false;

        $interactions = $this->getInteractions();
        foreach($interactions as $interaction){
			//build the interactions in the data variable
			$variables['data'] = preg_replace("/{".$interaction->getSerial()."}/", $interaction->toXHTML(), $variables['data']);

			if($interaction->getType() == 'upload'){
        		$variables['hasUpload'] = true;
        	}
        	if($interaction->getType() == 'slider'){
        		$variables['hasSlider'] = true;
        	}
       		if(!$variables['hasGraphics']){
				$variables['hasGraphics'] = $interaction->isGraphic();
			}
        }
		
        foreach($this->objects as $object) {
			$variables['data'] = preg_replace("/{".$object->getSerial()."}/", $object->toXHTML(), $variables['data']);
        }

        // get Matching data
        $matchingData = $this->getMatchingData();
		$variables['matching'] = array();
        $variables['matching']['data'] = $matchingData;
        $variables['matching']['url'] = ROOT_URL . "taoDelivery/ResultDelivery/evaluate";
        $variables['matching']['params'] = array ();


        $tplRenderer = new taoItems_models_classes_TemplateRenderer($template, $variables);
        $qtifolder = common_ext_ExtensionsManager::singleton()->getExtensionById('taoQTI')->getConstant('BASE_WWW');
        $itemsfolder = common_ext_ExtensionsManager::singleton()->getExtensionById('taoItems')->getConstant('BASE_WWW');
        $tplRenderer->setData('ctx_qti_base_www',		$qtifolder .'js/QTI/');
        $tplRenderer->setData('ctx_qti_lib_www',		$qtifolder .'js/QTI/');
        $tplRenderer->setData('ctx_qti_matching_www',	$itemsfolder .'js/taoMatching/');
        $returnValue = $tplRenderer->render();

        // section 127-0-1-1--56c234f4:12a31c89cc3:-8000:0000000000002388 end

        return (string) $returnValue;
    }

    /**
     * Short description of method toQTI
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @return string
     */
    public function toQTI()
    {
        $returnValue = (string) '';

        // section 127-0-1-1--56c234f4:12a31c89cc3:-8000:000000000000238A begin

        $template  = self::getTemplatePath() . '/qti.item.tpl.php';
    	$variables 	= $this->extractVariables();

		$variables['rowOptions']  	= $this->xmlizeOptions();
		$variables['response'] 		= '';
		$foundResponses = array();
		foreach($this->objects as $object){
			$variables['data'] = preg_replace("/{".$object->getSerial()."}/", $object->toQti(), $variables['data']);
		}
		foreach($this->getInteractions() as $interaction){

			//build the interactions in the data variable
			$variables['data'] = preg_replace("/{".$interaction->getSerial()."}/", $interaction->toQti(), $variables['data']);

			//build the response
			$response = $interaction->getResponse();

			if(!is_null($response)){
				if(!in_array($response->getIdentifier(), $foundResponses)){
					$variables['response'] .= $response->toQTI();
					$foundResponses[] = $response->getIdentifier();
				}
			}
		}

        // render the responseProcessing
        $renderedResponseProcessing = '';
        $responseProcessing = $this->getResponseProcessing();
        if(isset($responseProcessing)){
        	if ($responseProcessing instanceOf taoQTI_models_classes_QTI_response_TemplatesDriven) {
        		$renderedResponseProcessing = $responseProcessing->buildQTI($this);
        	} else {
				$renderedResponseProcessing = $responseProcessing->toQTI();
        	}
        }

        $variables['renderedResponseProcessing'] = $renderedResponseProcessing;

        $tplRenderer = new taoItems_models_classes_TemplateRenderer($template, $variables);

		//render and clean the xml
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $dom->preserveWhiteSpace = false;
        $dom->validateOnParse = false;
        $dom->loadXML($tplRenderer->render());
        $returnValue = $dom->saveXML();
		
        // section 127-0-1-1--56c234f4:12a31c89cc3:-8000:000000000000238A end

        return (string) $returnValue;
    }

    /**
     * Short description of method toForm
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @return tao_helpers_form_Form
     */
    public function toForm()
    {
        $returnValue = null;

        // section 127-0-1-1-25600304:12a5c17a5ca:-8000:0000000000002493 begin

		$formContainer = new taoQTI_actions_QTIform_AssessmentItem($this);
		$returnValue = $formContainer->getForm();

        // section 127-0-1-1-25600304:12a5c17a5ca:-8000:0000000000002493 end

        return $returnValue;
    }

    /**
     * Short description of method getMatchingData
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @return array
     */
    public function getMatchingData()
    {
        $returnValue = array();

        // section 127-0-1-1-554f2bd6:12c176484b7:-8000:0000000000002B2F begin

            $returnValue = array (
                "rule"      => null,
                "corrects"  => array(),
                "maps"      => array(),
                "areaMaps"      => array(),
                "outcomes"  => array()
            );

            // BUILD the RP rule
            if(!is_null($this->getResponseProcessing ())){
            	if ($this->getResponseProcessing() instanceof taoQTI_models_classes_QTI_response_TemplatesDriven) {
					$returnValue["rule"] = $this->getResponseProcessing()->buildRule($this);
            	} else {
					$returnValue["rule"] = $this->getResponseProcessing()->getRule();
            	}
            }

            // Get the correct responses (correct variables and map variables)
            $corrects = array ();
            $maps = array ();
            $interactions = $this->getInteractions();
            foreach ($interactions as $interaction){
            	if( $interaction->getResponse () != null){
	                $correctJSON = $interaction->getResponse ()->correctToJSON();
	                if ($correctJSON != null) {
	                    array_push ($returnValue["corrects"], $correctJSON);
	                }

                    $mapJson = $interaction->getResponse ()->mapToJSON();
                    if ($mapJson != null) {
                        array_push ($returnValue["maps"], $mapJson);
                    }

                    $areaMapJson = $interaction->getResponse ()->areaMapToJSON();
                    if ($areaMapJson != null) {
                        array_push ($returnValue["areaMaps"], $areaMapJson);
                    }
            	}
            }

            // Get the outcome variables
            $outcomes = $this->getOutcomes ();
            foreach ($outcomes as $outcome){
                array_push ($returnValue["outcomes"], $outcome->toJSON());
            }

        // section 127-0-1-1-554f2bd6:12c176484b7:-8000:0000000000002B2F end

        return (array) $returnValue;
    }

    /**
     * Short description of method addObject
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  Object object
     * @return mixed
     */
    public function addObject( taoQTI_models_classes_QTI_Object $object)
    {
        // section 127-0-1-1-2549921c:137563a02f1:-8000:0000000000003A84 begin
    	if(is_null($object)){
    		throw new common_exception_Error('called addObject with null object');
    	}
    	$this->objects[] = $object;
        // section 127-0-1-1-2549921c:137563a02f1:-8000:0000000000003A84 end
    }

    /**
     * Short description of method getObjects
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @return array
     */
    public function getObjects()
    {
        $returnValue = array();

        // section 127-0-1-1-2549921c:137563a02f1:-8000:0000000000003A8A begin
        $returnValue = $this->objects;
        // section 127-0-1-1-2549921c:137563a02f1:-8000:0000000000003A8A end

        return (array) $returnValue;
    }

    /**
     * Short description of method removeObject
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  Object object
     * @return boolean
     */
    public function removeObject( taoQTI_models_classes_QTI_Object $object)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--84650a0:1386bc9fc2f:-8000:000000000000A146 begin
			if(!is_null($object)){
    		if(isset($this->objects[$object->getSerial()])){
					//finally, delete the interaction:
    			$object->destroy();
    			unset($this->objects[$object->getSerial()]);

    			//allow responseProcessing to do cleanup
    			$returnValue = true;
    		}
    	}
        // section 127-0-1-1--84650a0:1386bc9fc2f:-8000:000000000000A146 end

        return (bool) $returnValue;
    }

} /* end of class taoQTI_models_classes_QTI_Item */