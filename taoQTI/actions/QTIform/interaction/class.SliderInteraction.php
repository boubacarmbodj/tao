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
 * TAO - taoItems\actions\QTIform\interaction\class.SliderInteraction.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 05.01.2011, 11:32:50 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Somsack SIPASSEUTH, <s.sipasseuth@gmail.com>
 * @package taoItems
 * @see http://www.imsglobal.org/question/qti_v2p0/imsqti_infov2p0.html#element10341
 * @subpackage actions_QTIform_interaction
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include taoQTI_actions_QTIform_interaction_BlockInteraction
 *
 * @author Somsack SIPASSEUTH, <s.sipasseuth@gmail.com>
 * @see http://www.imsglobal.org/question/qti_v2p0/imsqti_infov2p0.html#element10250
 */
require_once('taoQTI/actions/QTIform/interaction/class.BlockInteraction.php');

/* user defined includes */
// section 10-13-1-39--340dbb51:12d5574289f:-8000:0000000000002F75-includes begin
// section 10-13-1-39--340dbb51:12d5574289f:-8000:0000000000002F75-includes end

/* user defined constants */
// section 10-13-1-39--340dbb51:12d5574289f:-8000:0000000000002F75-constants begin
// section 10-13-1-39--340dbb51:12d5574289f:-8000:0000000000002F75-constants end

/**
 * Short description of class
 *
 * @access public
 * @author Somsack SIPASSEUTH, <s.sipasseuth@gmail.com>
 * @package taoItems
 * @see http://www.imsglobal.org/question/qti_v2p0/imsqti_infov2p0.html#element10341
 * @subpackage actions_QTIform_interaction
 */
class taoQTI_actions_QTIform_interaction_SliderInteraction
    extends taoQTI_actions_QTIform_interaction_BlockInteraction
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method initElements
     *
     * @access public
     * @author Somsack SIPASSEUTH, <s.sipasseuth@gmail.com>
     * @return mixed
     */
    public function initElements()
    {
        // section 10-13-1-39--340dbb51:12d5574289f:-8000:0000000000002F77 begin
		parent::setCommonElements();
		$interaction = $this->getInteraction();
		$this->form->addElement(taoQTI_actions_QTIform_AssessmentItem::createTextboxElement($interaction, 'lowerBound', __('Lower bound')));//mendatory 0
        $this->form->addElement(taoQTI_actions_QTIform_AssessmentItem::createTextboxElement($interaction, 'upperBound', __('Upper bound')));//mendatory 10
        $this->form->addElement(taoQTI_actions_QTIform_AssessmentItem::createTextboxElement($interaction, 'step', __('Step')));
		
		//the very optional step label attr is temporaily disabled in authoring since the runtime does not support it properly
        //$this->form->addElement(taoQTI_actions_QTIform_AssessmentItem::createBooleanElement($interaction, 'stepLabel', __('Step label display')));//false
		
		$orientationElt = tao_helpers_form_FormFactory::getElement('orientation', 'Combobox');
		$orientationElt->setDescription(__('Orientation'));
		$orientationElt->setOptions(array(
			'vertical' => __('vertical'),
			'horizontal' => __('horizontal')
		));
		$orientation = $interaction->getOption('orientation');
		if(!empty($orientation)){
			if($orientation === 'vertical' || $orientation === 'horizontal'){
				$orientationElt->setValue($orientation);
			}
		}
		$this->form->addElement($orientationElt);

		$this->form->addElement(taoQTI_actions_QTIform_AssessmentItem::createBooleanElement($interaction, 'reverse', __('Reverse rendering')));//false		
		// section 10-13-1-39--340dbb51:12d5574289f:-8000:0000000000002F77 end
    }

} /* end of class taoQTI_actions_QTIform_interaction_SliderInteraction */

?>