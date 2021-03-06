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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */

/**
 * Factory to prepare the ontology data for the
 * javascript generis tree
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 * @subpackage models_classes
 */
class tao_models_classes_GenerisTreeFactory
{
	/**
	 * builds the data for a generis tree
	 * 
	 * @param core_kernel_classes_Class $class
	 * @param boolean $showResources
	 * @param array $openNodes
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
    public function buildTree(core_kernel_classes_Class $class, $showResources, $openNodes = array(), $limit = 10, $offset = 0) {
    	return $this->classToNode($class, $showResources, $limit, $offset, $openNodes);
    }
	
    /**
     * Builds a class node including it's content
     * 
     * @param core_kernel_classes_Class $class
	 * @param boolean $showResources
     * @param int $limit
     * @param int $offset
     * @param array $openNodes
     * @return array
     */
    private function classToNode(core_kernel_classes_Class $class, $showResources, $limit, $offset, $openNodes) {
    	$label = $class->getLabel();
		$label = empty($label) ? __('no label') : $label;
		$returnValue = $this->buildClassNode($class);
		
		$instancesCount = 0;
		// only show the resources count if we allow resources to be viewed
		if ($showResources) {
			$instancesCount = (int) $class->countInstances();
			$returnValue['count'] = $instancesCount;
		}
		
		// allow the class to be opened if it contains either instances or subclasses
		if ($instancesCount > 0 || count($class->getSubClasses(false)) > 0) {
			if (in_array($class->getUri(), $openNodes)) {
				$returnValue['state']	= 'open';
				$returnValue['children'] = $this->buildChildNodes($class, $showResources, $limit, $offset, $openNodes);
			} else {
				$returnValue['state']	= 'closed';
			}
		}
		return $returnValue;
    }
    
    /**
     * Builds the content of a class node including it's content
     * 
     * @param core_kernel_classes_Class $class
	 * @param boolean $showResources
     * @param int $limit
     * @param int $offset
     * @param array $openNodes
     * @return array
     */
    private function buildChildNodes(core_kernel_classes_Class $class, $showResources, $limit, $offset, $openNodes) {
    	$childs = array();
    	// subclasses
		foreach ($class->getSubClasses(false) as $subclass) {
			$childs[] = $this->classToNode($subclass, $showResources, $limit, $offset, $openNodes);
		}
		// resources
    	if ($showResources) {
			$searchResult = $class->searchInstances(array(),array(
				'limit'		=> $limit,
				'offset'	=> $offset,
				'recursive'	=> false
			));
			
			foreach ($searchResult as $instance){
				$childs[] = $this->buildResourceNode($instance);
			}
		}
		return $childs;
    }
    
    /**
     * generis tree representation of a class node
     * without it's content
     * 
     * @param core_kernel_classes_Class $class
     * @return array
     */
    public function buildClassNode(core_kernel_classes_Class $class) {
    	$label = $class->getLabel();
		$label = empty($label) ? __('no label') : $label;
		return array(
			'data' 	=> $label,
			'type'	=> 'class',
			'attributes' => array(
				'id' => tao_helpers_Uri::encode($class->getUri()),
				'class' => 'node-class'
			)
		);
    }
    
    /**
     * generis tree representation of a resource node
     * 
     * @param core_kernel_classes_Resource $resource
     * @return array
     */
    public function buildResourceNode(core_kernel_classes_Resource $resource) {
		$label = $resource->getLabel();
		$label = empty($label) ? __('no label') : $label;

		return array(
			'data' 	=> tao_helpers_Display::textCutter($label, 16),
			'type'	=> 'instance',
			'attributes' => array(
				'id' => tao_helpers_Uri::encode($resource->getUri()),
				'class' => 'node-instance'
			)
		);
    }
    
	/**
	 * returns the nodes to open in order to display
	 * all the listed resources to be visible
	 * 
	 * @param array $resources list of resources to show
	 * @param core_kernel_classes_Class $rootNode root node of the tree
	 * @return array array of the uris of the nodes to open
	 */
    public static function getNodesToOpen($uris, core_kernel_classes_Class $rootNode) {
    	// this array is in the form of
    	// URI to test => array of uris that depend on the URI
    	$toTest = array();
    	foreach($uris as $uri){
    		$resource = new core_kernel_classes_Resource($uri);
    		foreach ($resource->getTypes() as $type) {
    			$toTest[$type->getUri()] = array();
    		}
		}
		$toOpen = array($rootNode->getUri());
		while (!empty($toTest)) {
			reset($toTest);
			list($classUri, $depends) = each($toTest);
			unset($toTest[$classUri]);
			if (in_array($classUri, $toOpen)) {
				$toOpen = array_merge($toOpen, $depends); 
			} else {
				$class = new core_kernel_classes_Class($classUri);
				foreach ($class->getParentClasses(false) as $parent) {
					if ($parent->getUri() == RDF_CLASS) {
						continue;
					}
					if (!isset($toTest[$parent->getUri()])) {
						$toTest[$parent->getUri()] = array();
					}
					$toTest[$parent->getUri()] = array_merge(
						$toTest[$parent->getUri()],
						array($classUri),
						$depends
					);
				}
			}
		}
		return $toOpen;
    }
    
}

?>