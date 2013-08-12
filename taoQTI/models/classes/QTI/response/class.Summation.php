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
 * TAO - taoQTI/models/classes/QTI/response/class.Summation.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 20.01.2012, 19:06:03 with ArgoUML PHP module 
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
 * include taoQTI_models_classes_QTI_response_Composite
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('taoQTI/models/classes/QTI/response/class.Composite.php');

/* user defined includes */
// section 127-0-1-1-786830e4:134f066fb13:-8000:000000000000901D-includes begin
// section 127-0-1-1-786830e4:134f066fb13:-8000:000000000000901D-includes end

/* user defined constants */
// section 127-0-1-1-786830e4:134f066fb13:-8000:000000000000901D-constants begin
// section 127-0-1-1-786830e4:134f066fb13:-8000:000000000000901D-constants end

/**
 * Short description of class taoQTI_models_classes_QTI_response_Summation
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package taoItems
 * @subpackage models_classes_QTI_response
 */
class taoQTI_models_classes_QTI_response_Summation
    extends taoQTI_models_classes_QTI_response_Composite
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method getCompositionRules
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return array
     */
    public function getCompositionRules()
    {
        $returnValue = array();

        // section 127-0-1-1-4c0a0972:134fa47975d:-8000:0000000000003606 begin
        $subExpressions = array();
        foreach ($this->components as $irp) {
        	$subExpressions[] = new taoQTI_models_classes_QTI_expression_CommonExpression('variable', array('identifier' => $irp->getOutcome()->getIdentifier()));
        }
        $sum = new taoQTI_models_classes_QTI_expression_CommonExpression('sum', array());
        $sum->setSubExpressions($subExpressions);
        $summationRule = new taoQTI_models_classes_QTI_response_SetOutcomeVariable($this->outcomeIdentifier, $sum);
        
        $returnValue = array($summationRule);
        // section 127-0-1-1-4c0a0972:134fa47975d:-8000:0000000000003606 end

        return (array) $returnValue;
    }

    /**
     * Short description of method getCompositionQTI
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return string
     */
    public function getCompositionQTI()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-4c0a0972:134fa47975d:-8000:0000000000003632 begin
        $returnValue .= '<setOutcomeValue identifier="'.$this->outcomeIdentifier.'"><sum>';
        foreach ($this->components as $irp) {
        	$returnValue .= '<variable identifier="'.$irp->getOutcome()->getIdentifier().'" />';
        }
        $returnValue .= '</sum></setOutcomeValue>';
        // section 127-0-1-1-4c0a0972:134fa47975d:-8000:0000000000003632 end

        return (string) $returnValue;
    }

} /* end of class taoQTI_models_classes_QTI_response_Summation */

?>