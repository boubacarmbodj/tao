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
 * TAO - taoItems\actions\QTIform\choice\class.AssociableChoice.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 05.01.2011, 11:32:51 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Somsack SIPASSEUTH, <s.sipasseuth@gmail.com>
 * @package taoItems
 * @see http://www.imsglobal.org/question/qti_v2p0/imsqti_infov2p0.html#element10257
 * @subpackage actions_QTIform_choice
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include taoQTI_actions_QTIform_choice_Choice
 *
 * @author Somsack SIPASSEUTH, <s.sipasseuth@gmail.com>
 * @see http://www.imsglobal.org/question/qti_v2p0/imsqti_infov2p0.html#element10254
 */
require_once('taoQTI/actions/QTIform/choice/class.Choice.php');

/* user defined includes */
// section 10-13-1-39-643eb156:12d51696e7c:-8000:0000000000005013-includes begin
// section 10-13-1-39-643eb156:12d51696e7c:-8000:0000000000005013-includes end

/* user defined constants */
// section 10-13-1-39-643eb156:12d51696e7c:-8000:0000000000005013-constants begin
// section 10-13-1-39-643eb156:12d51696e7c:-8000:0000000000005013-constants end

/**
 * Short description of class taoQTI_actions_QTIform_choice_AssociableChoice
 *
 * @abstract
 * @access public
 * @author Somsack SIPASSEUTH, <s.sipasseuth@gmail.com>
 * @package taoItems
 * @see http://www.imsglobal.org/question/qti_v2p0/imsqti_infov2p0.html#element10257
 * @subpackage actions_QTIform_choice
 */
abstract class taoQTI_actions_QTIform_choice_AssociableChoice
    extends taoQTI_actions_QTIform_choice_Choice
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute interaction
     *
     * @access protected
     * @var Interaction
     */
    protected $interaction = null;

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Somsack SIPASSEUTH, <s.sipasseuth@gmail.com>
     * @param  Data choice
     */
    public function __construct( taoQTI_models_classes_QTI_Data $choice)
    {
        // section 10-13-1-39-643eb156:12d51696e7c:-8000:0000000000005014 begin
		
		$qtiService = taoQTI_models_classes_QTI_Service::singleton();
		$interaction = $qtiService->getComposingData($choice);
		if($interaction instanceof taoQTI_models_classes_QTI_Interaction){
			$this->interaction = $interaction;
		}else{
			throw new common_exception_Error('cannot find the parent interaction of the current choice');
		}
		
		$returnValue = parent::__construct($choice);
		
        // section 10-13-1-39-643eb156:12d51696e7c:-8000:0000000000005014 end
    }

    /**
     * Short description of method setCommonElements
     *
     * @access public
     * @author Somsack SIPASSEUTH, <s.sipasseuth@gmail.com>
     */
    public function setCommonElements()
    {
        // section 10-13-1-39-643eb156:12d51696e7c:-8000:000000000000501A begin
		
		parent::setCommonElements();
		
		$matchGroupElt = tao_helpers_form_FormFactory::getElement('matchGroup', 'Checkbox');
		$matchGroupElt->setDescription(__('Match Group'));
		$matchGroupOption = $this->getMatchGroupOptions();
		if(!empty($matchGroupOption)){
			$matchGroupElt->setOptions($matchGroupOption);
			
			if($this->choice instanceof taoQTI_models_classes_QTI_Choice){
				$matchGroups = $this->choice->getOption('matchGroup');
				if(!empty($matchGroups)){
					if(is_array($matchGroups)){
						foreach($matchGroups as $choiceIdentifierOrSerial){
							$matchGroupElt->setValue($choiceIdentifierOrSerial);
						}
					}else{
						$matchGroupElt->setValue((string)$matchGroups);
					}
				}
			}else if($this->choice instanceof taoQTI_models_classes_QTI_Group){
				foreach($this->choice->getChoices() as $choiceSerial){
					$choice = taoQTI_models_classes_QTI_Service::getDataBySerial($choiceSerial, 'taoQTI_models_classes_QTI_Choice');
					$matchGroupElt->setValue($choice->getIdentifier());
					// $matchGroupElt->setValue($choiceSerial);
				}
			}
			
			$this->form->addElement($matchGroupElt);
		}
		
        // section 10-13-1-39-643eb156:12d51696e7c:-8000:000000000000501A end
    }

    /**
     * Short description of method getMatchGroupOptions
     *
     * @abstract
     * @access protected
     * @author Somsack SIPASSEUTH, <s.sipasseuth@gmail.com>
     * @return array
     */
    protected abstract function getMatchGroupOptions();

} /* end of abstract class taoQTI_actions_QTIform_choice_AssociableChoice */

?>