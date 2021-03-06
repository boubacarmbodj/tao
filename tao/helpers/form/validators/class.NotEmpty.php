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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/validators/class.NotEmpty.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 22.12.2011, 14:51:41 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage helpers_form_validators
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * The validators enable you to perform a validation callback on a form element.
 * It's provide a model of validation and must be overriden.
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('tao/helpers/form/class.Validator.php');

/* user defined includes */
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BCA-includes begin
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BCA-includes end

/* user defined constants */
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BCA-constants begin
// section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BCA-constants end

/**
 * Short description of class tao_helpers_form_validators_NotEmpty
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage helpers_form_validators
 */
class tao_helpers_form_validators_NotEmpty
    extends tao_helpers_form_Validator
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BEA begin
		
		parent::__construct($options);
		
		(isset($options['message'])) ? $this->message = $options['message'] : $this->message = __('This field is required');
		
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BEA end
    }

    /**
     * Short description of method evaluate
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  values
     * @return boolean
     */
    public function evaluate($values)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BCB begin
        if (is_string($values)){
        	$values = trim($values);
        }
        
		if (!empty($values)){
			if(count($values) >= 1){
				$returnValue = true;
			}
			else{
				$value = trim($values);
				if(!empty($value)){
					$returnValue = true;
				}
			}
		}
		else{
			$returnValue = false;
		}
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BCB end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_form_validators_NotEmpty */

?>