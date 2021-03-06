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
 * Poly represents a polygon managed by the tao matching api.
 * A polygon hosts a list points. 
 * A point is represented on the system by a "Tuple Variable"
 * of two integers.
 *
 * @author Cedric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package taoItems
 * @subpackage models_classes_Matching
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Shape represents the different shapres managed by the tao 
 * matching api.
 *
 * @author Cedric Alfonsi, <cedric.alfonsi@tudor.lu>
 */
require_once('taoQTI/models/classes/Matching/class.Shape.php');

/* user defined includes */
// section 127-0-1-1--1f4c3271:12ce9f13e78:-8000:0000000000002CBA-includes begin
// section 127-0-1-1--1f4c3271:12ce9f13e78:-8000:0000000000002CBA-includes end

/* user defined constants */
// section 127-0-1-1--1f4c3271:12ce9f13e78:-8000:0000000000002CBA-constants begin
// section 127-0-1-1--1f4c3271:12ce9f13e78:-8000:0000000000002CBA-constants end

/**
 * Poly represents a polygon managed by the tao matching api.
 * A polygon hosts a list points. 
 * A point is represented on the system by a "Tuple Variable"
 * of two integers.
 *
 * @access public
 * @author Cedric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package taoItems
 * @subpackage models_classes_Matching
 */
class taoQTI_models_classes_Matching_Poly
    extends taoQTI_models_classes_Matching_Shape
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * List of points which form the polygon
     *
     * @access protected
     * @var array
     */
    protected $points = array();

    // --- OPERATIONS ---

    /**
     * Check if the polygon contains a point
     *
     * @access public
     * @author Cedric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  Variable var The point to find.
The point is represented by a "Tuple Variable" of two integers.
     * @return boolean
     */
    public function contains( taoQTI_models_classes_Matching_Variable $var)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--1f4c3271:12ce9f13e78:-8000:0000000000002CBE begin
        
        list ($point_x, $point_y) = $var->getValue();

        // Algorithm from http://jsfromhell.com/math/is-point-in-poly
        for($c = false, $i = -1, $l = count($this->points), $j = $l - 1; ++$i < $l; $j = $i) {
            list ($point_i_x, $point_i_y) = $this->points[$i]->getValue();
            list ($point_j_x, $point_j_y) = $this->points[$j]->getValue();

            ( (($point_i_y->getValue() <= $point_y->getValue()) && ($point_y->getValue() < $point_j_y->getValue()))
                || (($point_j_y->getValue() <= $point_y->getValue() ) && ($point_y->getValue() < $point_i_y->getValue()))
            )
            && ($point_x->getValue() < ( ($point_j_x->getValue() - $point_i_x->getValue()) * ($point_y->getValue() - $point_i_y->getValue()) / ($point_j_y->getValue() - $point_i_y->getValue()) + $point_i_x->getValue()))
            && ($c = !$c);
        }
        
        $returnValue = $c;
        
        // section 127-0-1-1--1f4c3271:12ce9f13e78:-8000:0000000000002CBE end

        return (bool) $returnValue;
    }

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Cedric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  data
     * @return mixed
     */
    public function __construct(   $data)
    {
        // section 127-0-1-1--1f4c3271:12ce9f13e78:-8000:0000000000002CC4 begin
                
        if (isset($data->type)){
            $this->setType ($data->type);
        }
        
        foreach ($data->points as $point){
            array_push( $this->points, taoQTI_models_classes_Matching_VariableFactory::create($point));
        }
        
        // section 127-0-1-1--1f4c3271:12ce9f13e78:-8000:0000000000002CC4 end
    }

    /**
     * Get all the points of the polygon
     *
     * @access public
     * @author Cedric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return array
     */
    public function getPoints()
    {
        $returnValue = array();

        // section 127-0-1-1--1f4c3271:12ce9f13e78:-8000:0000000000002CD4 begin
        
        return $this->points;
        
        // section 127-0-1-1--1f4c3271:12ce9f13e78:-8000:0000000000002CD4 end

        return (array) $returnValue;
    }

} /* end of class taoQTI_models_classes_Matching_Poly */

?>