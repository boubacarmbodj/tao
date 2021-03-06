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
 * TAO - taoItems\actions\QTIform\interaction\class.ExtendedtextInteraction.php
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
 * @see http://www.imsglobal.org/question/qti_v2p0/imsqti_infov2p0.html#element10304
 * @subpackage actions_QTIform_interaction
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include taoQTI_actions_QTIform_interaction_StringInteraction
 *
 * @author Somsack SIPASSEUTH, <s.sipasseuth@gmail.com>
 * @see http://www.imsglobal.org/question/qti_v2p0/imsqti_infov2p0.html#element10297
 */
require_once('taoQTI/actions/QTIform/interaction/class.StringInteraction.php');

/* user defined includes */
// section 10-13-1-39-643eb156:12d51696e7c:-8000:0000000000005094-includes begin
// section 10-13-1-39-643eb156:12d51696e7c:-8000:0000000000005094-includes end

/* user defined constants */
// section 10-13-1-39-643eb156:12d51696e7c:-8000:0000000000005094-constants begin
// section 10-13-1-39-643eb156:12d51696e7c:-8000:0000000000005094-constants end

/**
 * Short description of class
 *
 * @access public
 * @author Somsack SIPASSEUTH, <s.sipasseuth@gmail.com>
 * @package taoItems
 * @see http://www.imsglobal.org/question/qti_v2p0/imsqti_infov2p0.html#element10304
 * @subpackage actions_QTIform_interaction
 */
class taoQTI_actions_QTIform_interaction_ExtendedtextInteraction
    extends taoQTI_actions_QTIform_interaction_StringInteraction
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
        // section 10-13-1-39-643eb156:12d51696e7c:-8000:0000000000005095 begin
		
		$interaction = $this->getInteraction();
		
		//the prompt field is the interaction's data for a block interaction: an extend text interaction is both a "string" and a "block" interaction
		$promptElt = tao_helpers_form_FormFactory::getElement('prompt', 'Textarea');//should be a text... need to solve the conflict with the 
		$promptElt->setAttribute('class', 'qti-html-area');
		$promptElt->setDescription(__('Prompt'));
		// $promptElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));//no validator required for prompt
		$interactionData = $this->interaction->getPrompt();
		if(!empty($interactionData)){
			$promptElt->setValue($interactionData);
		}
		$this->form->addElement($promptElt);
		
		//set common elements of string interaction:
		parent::setCommonElements();
				
		//other elements:		
		$maxStringsElt = tao_helpers_form_FormFactory::getElement('maxStrings', 'Textbox');
		$maxStringsElt->setDescription(__('Maximum number of separate strings'));
		//validator: is int??
		$maxStrings = $interaction->getOption('maxStrings');
		if(!empty($maxStrings)){
			$maxStringsElt->setValue($maxStrings);
		}
		$this->form->addElement($maxStringsElt);
		
		$expectedLinesElt = tao_helpers_form_FormFactory::getElement('expectedLines', 'Textbox');
		$expectedLinesElt->setDescription(__('Expected lines'));
		$expectedLines = $interaction->getOption('expectedLines');
		if(!empty($expectedLines)){
			$expectedLinesElt->setValue($expectedLines);
		}
		$this->form->addElement($expectedLinesElt);
		
        // section 10-13-1-39-643eb156:12d51696e7c:-8000:0000000000005095 end
    }

} /* end of class taoQTI_actions_QTIform_interaction_ExtendedtextInteraction */

?>