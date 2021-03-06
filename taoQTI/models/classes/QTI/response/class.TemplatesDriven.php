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
?>
<?php

error_reporting(E_ALL);

/**
 * TAO - taoQTI/models/classes/QTI/response/class.TemplatesDriven.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 09.02.2012, 16:25:40 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package taoItems
 * @subpackage models_classes_QTI_response
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include taoQTI_models_classes_QTI_response_ResponseProcessing
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('taoQTI/models/classes/QTI/response/class.ResponseProcessing.php');

/**
 * include taoQTI_models_classes_QTI_response_Rule
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('taoQTI/models/classes/QTI/response/interface.Rule.php');

/* user defined includes */
// section 127-0-1-1-703c736:12c63695364:-8000:0000000000002BF2-includes begin
// section 127-0-1-1-703c736:12c63695364:-8000:0000000000002BF2-includes end

/* user defined constants */
// section 127-0-1-1-703c736:12c63695364:-8000:0000000000002BF2-constants begin
// section 127-0-1-1-703c736:12c63695364:-8000:0000000000002BF2-constants end

/**
 * Short description of class
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package taoItems
 * @subpackage models_classes_QTI_response
 */
class taoQTI_models_classes_QTI_response_TemplatesDriven
    extends taoQTI_models_classes_QTI_response_ResponseProcessing
        implements taoQTI_models_classes_QTI_response_Rule
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method getRule
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return string
     */
    public function getRule()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-3397f61e:12c15e8566c:-8000:0000000000002AFF begin
        throw new common_exception_Error('please use buildRule for templateDriven instead');
        /*
		foreach ($this->templateMap as $identifier => $uri){
			
			$templateName = substr($uri, strrpos($uri, '/')+1);
			$matchingTemplate  = dirname(__FILE__).'/rpTemplate/rule.'.$templateName.'.tpl.php';

			$tplRenderer = new taoItems_models_classes_TemplateRenderer(
				$matchingTemplate,
				Array('responseIdentifier' => $identifier, 'outcomeIdentifier'=>'SCORE')
			);    
			$returnValue .= $tplRenderer->render();
		}*/
        // section 127-0-1-1-3397f61e:12c15e8566c:-8000:0000000000002AFF end

        return (string) $returnValue;
    }

    /**
     * Short description of method isSupportedTemplate
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string uri
     * @return taoQTI_models_classes_Matching_bool
     */
    public static function isSupportedTemplate($uri)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-703c736:12c63695364:-8000:0000000000002BFD begin
        
        $mythoMap = Array (
        	taoQTI_models_classes_QTI_response_Template::MATCH_CORRECT,
        	taoQTI_models_classes_QTI_response_Template::MAP_RESPONSE,
        	taoQTI_models_classes_QTI_response_Template::MAP_RESPONSE_POINT
        );
        
        $returnValue = in_array($uri, $mythoMap);
        
        // section 127-0-1-1-703c736:12c63695364:-8000:0000000000002BFD end

        return (bool) $returnValue;
    }

    /**
     * Short description of method create
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Item item
     * @return taoQTI_models_classes_QTI_response_ResponseProcessing
     */
    public static function create( taoQTI_models_classes_QTI_Item $item)
    {
        $returnValue = null;

        // section 127-0-1-1-6f11fd4b:1350ab5145f:-8000:000000000000360F begin
		$returnValue = new taoQTI_models_classes_QTI_response_TemplatesDriven();
        if(count($item->getOutcomes()) == 0){
			$item->setOutcomes(array(
				new taoQTI_models_classes_QTI_Outcome('SCORE', array('baseType' => 'integer', 'cardinality' => 'single'))
			));
		}
		foreach ($item->getInteractions() as $interaction) {
			$returnValue->setTemplate($interaction->getResponse(), taoQTI_models_classes_QTI_response_Template::MATCH_CORRECT);
		}
        // section 127-0-1-1-6f11fd4b:1350ab5145f:-8000:000000000000360F end

        return $returnValue;
    }

    /**
     * Short description of method takeOverFrom
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  ResponseProcessing responseProcessing
     * @param  Item item
     * @return taoQTI_models_classes_QTI_response_ResponseProcessing
     */
    public static function takeOverFrom( taoQTI_models_classes_QTI_response_ResponseProcessing $responseProcessing,  taoQTI_models_classes_QTI_Item $item)
    {
        $returnValue = null;

        // section 127-0-1-1-6f11fd4b:1350ab5145f:-8000:0000000000003615 begin
        if ($responseProcessing instanceof self)
        	return $responseProcessing;
        	
        if ($responseProcessing instanceof taoQTI_models_classes_QTI_response_Template) {
			$returnValue = new taoQTI_models_classes_QTI_response_TemplatesDriven();
        	// theoretic only interaction 'RESPONSE' should be considered
			foreach ($item->getInteractions() as $interaction) {
				$returnValue->setTemplate($interaction->getResponse(), $responseProcessing->getUri());
			}
	        	
        } else {
        	throw new taoQTI_models_classes_QTI_response_TakeoverFailedException();
        }
        // section 127-0-1-1-6f11fd4b:1350ab5145f:-8000:0000000000003615 end

        return $returnValue;
    }

    /**
     * Short description of method setTemplate
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Response response
     * @param  string template
     * @return boolean
     */
    public function setTemplate( taoQTI_models_classes_QTI_Response $response, $template)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-53d7bbd:135145c7d03:-8000:0000000000003696 begin
        $response->setHowMatch($template);
        $returnValue = true;
        // section 127-0-1-1-53d7bbd:135145c7d03:-8000:0000000000003696 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method getTemplate
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Response response
     * @return string
     */
    public function getTemplate( taoQTI_models_classes_QTI_Response $response)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-4c0a0972:134fa47975d:-8000:000000000000360D begin
        $returnValue = $response->getHowMatch();
        // section 127-0-1-1-4c0a0972:134fa47975d:-8000:000000000000360D end

        return (string) $returnValue;
    }

    /**
     * Short description of method takeNoticeOfAddedInteraction
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Interaction interaction
     * @param  Item item
     * @return mixed
     */
    public function takeNoticeOfAddedInteraction( taoQTI_models_classes_QTI_Interaction $interaction,  taoQTI_models_classes_QTI_Item $item)
    {
        // section 127-0-1-1-53d7bbd:135145c7d03:-8000:000000000000365C begin
        $interaction->getResponse()->setHowMatch(taoQTI_models_classes_QTI_response_Template::MATCH_CORRECT);
        // section 127-0-1-1-53d7bbd:135145c7d03:-8000:000000000000365C end
    }

    /**
     * Short description of method takeNoticeOfRemovedInteraction
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Interaction interaction
     * @param  Item item
     * @return mixed
     */
    public function takeNoticeOfRemovedInteraction( taoQTI_models_classes_QTI_Interaction $interaction,  taoQTI_models_classes_QTI_Item $item)
    {
        // section 127-0-1-1-53d7bbd:135145c7d03:-8000:000000000000368F begin
        // section 127-0-1-1-53d7bbd:135145c7d03:-8000:000000000000368F end
    }

    /**
     * Short description of method getForm
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Response response
     * @return tao_helpers_form_Form
     */
    public function getForm( taoQTI_models_classes_QTI_Response $response)
    {
        $returnValue = null;

        // section 127-0-1-1-53d7bbd:135145c7d03:-8000:000000000000368C begin
		$formContainer = new taoQTI_actions_QTIform_TemplatesDrivenResponseOptions($this, $response);
        $returnValue = $formContainer->getForm();
        // section 127-0-1-1-53d7bbd:135145c7d03:-8000:000000000000368C end

        return $returnValue;
    }

    /**
     * Short description of method buildQTI
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Item item
     * @return string
     */
    public function buildQTI( taoQTI_models_classes_QTI_Item $item)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-703c736:12c63695364:-8000:0000000000002BF7 begin
    	if (count($item->getInteractions()) == 1) {
			foreach($item->getInteractions() as $interaction){
				$uri = $interaction->getResponse()->getHowMatch();
				$responseProcessingToRender = new taoQTI_models_classes_QTI_response_Template($uri);
				$returnValue = $responseProcessingToRender->toQTI();
			}
		} else {
	        $returnValue = "<responseProcessing>";
	        foreach ($item->getInteractions() as $interaction) {
	        	$response = $interaction->getResponse();
	        	$uri = $response->getHowMatch();
	        	$templateName = substr($uri, strrpos($uri, '/')+1);
		        $matchingTemplate  = dirname(__FILE__).'/rpTemplate/qti.'.$templateName.'.tpl.php';
		
		        $tplRenderer = new taoItems_models_classes_TemplateRenderer($matchingTemplate, Array(
	                                    'responseIdentifier'=> $response->getIdentifier()
	                                    , 'outcomeIdentifier'=>'SCORE'
	                                ));
		        $returnValue .= $tplRenderer->render();
		        
	        }
	        $returnValue .= "</responseProcessing>";
		}        
        // section 127-0-1-1-703c736:12c63695364:-8000:0000000000002BF7 end

        return (string) $returnValue;
    }

    /**
     * Short description of method buildRule
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Item item
     * @return string
     */
    public function buildRule( taoQTI_models_classes_QTI_Item $item)
    {
        $returnValue = (string) '';

        // section 127-0-1-1--606491e9:13562b595c4:-8000:00000000000037CE begin
		foreach ($item->getInteractions() as $interaction){
			$response = $interaction->getResponse();
			$uri = $response->getHowMatch();
			$templateName = substr($uri, strrpos($uri, '/')+1);
			$matchingTemplate  = dirname(__FILE__).'/rpTemplate/rule.'.$templateName.'.tpl.php';

			$tplRenderer = new taoItems_models_classes_TemplateRenderer(
				$matchingTemplate,
				Array('responseIdentifier' => $response->getIdentifier(), 'outcomeIdentifier'=>'SCORE')
			);    
			$returnValue .= $tplRenderer->render();
        
		}
        // section 127-0-1-1--606491e9:13562b595c4:-8000:00000000000037CE end

        return (string) $returnValue;
    }

    /**
     * Short description of method toQTI
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return string
     */
    public function toQTI()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-703c736:12c63695364:-8000:0000000000002C06 begin
        throw new common_exception_Error('please use buildQTI for templateDriven instead');
        /*
		if (count($this->templateMap) == 1) {
			foreach($this->templateMap as $uri){
				$responseProcessingToRender = new taoQTI_models_classes_QTI_response_Template($uri);
				$returnValue = $responseProcessingToRender->toQTI();
			}
		} else {
	        $returnValue = "<responseProcessing>";
	        foreach ($this->templateMap as $identifier => $templateName) {
	        	$returnValue .= $this->buildQTI($templateName, Array(
	                                    'responseIdentifier'=> $identifier
	                                    , 'outcomeIdentifier'=>'SCORE'
	                                ));
	        }
	        $returnValue .= "</responseProcessing>";
		}
		*/
        // section 127-0-1-1-703c736:12c63695364:-8000:0000000000002C06 end

        return (string) $returnValue;
    }

} /* end of class taoQTI_models_classes_QTI_response_TemplatesDriven */

?>