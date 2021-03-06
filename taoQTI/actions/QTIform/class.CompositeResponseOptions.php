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
 * TAO - taoQTI/actions/QTIform/class.CompositeResponseOptions.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 25.01.2012, 15:52:32 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package taoItems
 * @subpackage actions_QTIform
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * This class provide a container for a specific form instance.
 * It's subclasses instanciate a form and it's elements to be used as a
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('tao/helpers/form/class.FormContainer.php');

/* user defined includes */
// section 127-0-1-1-7fd95e33:1350eecc263:-8000:0000000000003639-includes begin
// section 127-0-1-1-7fd95e33:1350eecc263:-8000:0000000000003639-includes end

/* user defined constants */
// section 127-0-1-1-7fd95e33:1350eecc263:-8000:0000000000003639-constants begin
// section 127-0-1-1-7fd95e33:1350eecc263:-8000:0000000000003639-constants end

/**
 * Short description of class taoQTI_actions_QTIform_CompositeResponseOptions
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package taoItems
 * @subpackage actions_QTIform
 */
class taoQTI_actions_QTIform_CompositeResponseOptions
    extends tao_helpers_form_FormContainer
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute responseProcessing
     *
     * @access public
     * @var ResponseProcessing
     */
    public $responseProcessing = null;

    /**
     * Short description of attribute response
     *
     * @access public
     * @var Response
     */
    public $response = null;

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  ResponseProcessing responseProcessing
     * @param  Response response
     * @return mixed
     */
    public function __construct( taoQTI_models_classes_QTI_response_ResponseProcessing $responseProcessing,  taoQTI_models_classes_QTI_Response $response)
    {
        // section 127-0-1-1-7fd95e33:1350eecc263:-8000:000000000000363B begin
		$this->responseProcessing = $responseProcessing;
        $this->response = $response;
        parent::__construct();
        // section 127-0-1-1-7fd95e33:1350eecc263:-8000:000000000000363B end
    }

    /**
     * Short description of method initForm
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function initForm()
    {
        // section 127-0-1-1-7fd95e33:1350eecc263:-8000:000000000000363F begin
        $this->form = tao_helpers_form_FormFactory::getForm('InteractionResponseProcessingForm');
		$this->form->setActions(array(), 'bottom');
        // section 127-0-1-1-7fd95e33:1350eecc263:-8000:000000000000363F end
    }

    /**
     * Short description of method initElements
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function initElements()
    {
        // section 127-0-1-1-7fd95e33:1350eecc263:-8000:0000000000003641 begin
        $rpElt = tao_helpers_form_FormFactory::getElement('responseprocessingSerial', 'Hidden');
		$rpElt->setValue($this->responseProcessing->getSerial());
		$this->form->addElement($rpElt);
		
    	$serialElt = tao_helpers_form_FormFactory::getElement('responseSerial', 'Hidden');
		$serialElt->setValue($this->response->getSerial());
		$this->form->addElement($serialElt);
    	
		$currentClass = get_class($this->responseProcessing->getInteractionResponseProcessing($this->response));
		$currentIRP = $currentClass::CLASS_ID;
		
		$irps = array(
			taoQTI_models_classes_QTI_response_interactionResponseProcessing_MatchCorrectTemplate::CLASS_ID => __('correct'),
		);
		
		//get interaction type:
		$qtiService = taoQTI_models_classes_QTI_Service::singleton();
		$interaction = $qtiService->getComposingData($this->response);
		if(!is_null($interaction)){
			switch(strtolower($interaction->getType())){
				case 'order':
				case 'graphicorder':{
					break;
				}
				case 'selectpoint';
				case 'positionobject':{
					$irps[taoQTI_models_classes_QTI_response_interactionResponseProcessing_MapResponsePointTemplate::CLASS_ID] = __('map point');
					break;
				}
				default:{
					$irps[taoQTI_models_classes_QTI_response_interactionResponseProcessing_MapResponseTemplate::CLASS_ID] = __('map');
				}
			}
		}
		
		if ($currentIRP == taoQTI_models_classes_QTI_response_interactionResponseProcessing_Custom::CLASS_ID) {
			$irps[taoQTI_models_classes_QTI_response_interactionResponseProcessing_Custom::CLASS_ID] = __('custom');			
		}
		
		if (common_ext_ExtensionsManager::singleton()->getExtensionById('taoCoding')->isEnabled()
			|| $currentIRP == taoQTI_models_classes_QTI_response_interactionResponseProcessing_None::CLASS_ID) {
				$irps[taoQTI_models_classes_QTI_response_interactionResponseProcessing_None::CLASS_ID] = __('manual');
		}			
		
		$InteractionResponseProcessing = tao_helpers_form_FormFactory::getElement('interactionResponseProcessing', 'Combobox');
		$InteractionResponseProcessing->setDescription(__('Processing type'));
		$InteractionResponseProcessing->setOptions($irps);
		$InteractionResponseProcessing->setValue($currentIRP);
		$this->form->addElement($InteractionResponseProcessing);
        // section 127-0-1-1-7fd95e33:1350eecc263:-8000:0000000000003641 end
    }

} /* end of class taoQTI_actions_QTIform_CompositeResponseOptions */

?>