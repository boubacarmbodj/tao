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
 * Specialize the export for the items
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package taoItems
 * @subpackage actions_form
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * This container initialize the export form.
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('tao/actions/form/class.Export.php');

/* user defined includes */
// section 127-0-1-1-70b2308e:12ca2398ae8:-8000:000000000000293A-includes begin
// section 127-0-1-1-70b2308e:12ca2398ae8:-8000:000000000000293A-includes end

/* user defined constants */
// section 127-0-1-1-70b2308e:12ca2398ae8:-8000:000000000000293A-constants begin
// section 127-0-1-1-70b2308e:12ca2398ae8:-8000:000000000000293A-constants end

/**
 * Specialize the export for the items
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package taoItems
 * @subpackage actions_form
 */
class taoItems_actions_form_Export
    extends tao_actions_form_Export
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * overriden
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function initElements()
    {
        // section 127-0-1-1-2c41a4d:12ca27f7d37:-8000:0000000000002921 begin

    	parent::initElements();

        // section 127-0-1-1-2c41a4d:12ca27f7d37:-8000:0000000000002921 end
    }

    /**
     * Create the form elements to select the instances to be exported
     *
     * @access protected
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    protected function initXMLElements()
    {
        // section 127-0-1-1-70b2308e:12ca2398ae8:-8000:000000000000293C begin

    	$itemService = taoItems_models_classes_ItemsService::singleton();

		$fileName = '';
    	$options = array();
    	if(isset($this->data['instance'])){
    		$item = $this->data['instance'];
    		if($item instanceof core_kernel_classes_Resource){
    			$fileName = strtolower(tao_helpers_Display::textCleaner($item->getLabel()));
    			$options[$item->uriResource] = $item->getLabel();
    		}
    	}
    	else {
    		if(isset($this->data['class'])){
	    		$class = $this->data['class'];
	    	}
	    	else{
	    		$class = $itemService->getRootClass();
	    	}
    		if($class instanceof core_kernel_classes_Class){
				$fileName =  strtolower(tao_helpers_Display::textCleaner($class->getLabel(), '*'));
				foreach($class->getInstances() as $instance){
					$options[$instance->uriResource] = $instance->getLabel();
				}
    		}
    	}

    	$descElt = tao_helpers_form_FormFactory::getElement('xml_desc', 'Label');
		$descElt->setValue(__("Enables you to export a ZIP archive containing one folder for each exported item. Each  folder is composed by a main XML file (the item's data) and  by externals resources (media, manifests, etc.)"));
		$this->form->addElement($descElt);

		$nameElt = tao_helpers_form_FormFactory::getElement('filename', 'Textbox');
		$nameElt->setDescription(__('File name'));
		$nameElt->setValue($fileName);
		$nameElt->setUnit(".zip");
		$nameElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
    	$this->form->addElement($nameElt);

    	$instanceElt = tao_helpers_form_FormFactory::getElement('instances', 'Checkbox');
    	$instanceElt->setDescription(__('Items'));
    	$instanceElt->setAttribute('checkAll', true);
		$instanceElt->setOptions(tao_helpers_Uri::encodeArray($options, tao_helpers_Uri::ENCODE_ARRAY_KEYS));
    	foreach(array_keys($options) as $value){
			$instanceElt->setValue($value);
		}
		$this->form->addElement($instanceElt);



    	$this->form->createGroup('options', __('Export Options'), array('xml_desc', 'filename', 'instances'));

        // section 127-0-1-1-70b2308e:12ca2398ae8:-8000:000000000000293C end
    }

    /**
     * Create the form elements to select the QTI instances to be exported
     *
     * @access protected
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    protected function initIMSCPElements()
    {
        // section 127-0-1-1--46051fb4:12ee209629f:-8000:0000000000002D57 begin

    	$itemService = taoItems_models_classes_ItemsService::singleton();

		$fileName = '';
    	$options = array();
    	if(isset($this->data['instance'])){
    		$item = $this->data['instance'];
    		if($item instanceof core_kernel_classes_Resource){
    			if($itemService->hasItemModel($item, array(TAO_ITEM_MODEL_QTI))){
    				$fileName = strtolower(tao_helpers_Display::textCleaner($item->getLabel()));
    				$options[$item->uriResource] = $item->getLabel();
    			}
    		}
    	}
    	else {
    		if(isset($this->data['class'])){
	    		$class = $this->data['class'];
	    	}
	    	else{
	    		$class = $itemService->getRootClass();
	    	}
    		if($class instanceof core_kernel_classes_Class){
					$fileName =  strtolower(tao_helpers_Display::textCleaner($class->getLabel(), '*'));
					foreach($class->getInstances() as $instance){
						if($itemService->hasItemModel($instance, array(TAO_ITEM_MODEL_QTI))){
							$options[$instance->uriResource] = $instance->getLabel();
						}
					}
    		}
    	}

		$descElt = tao_helpers_form_FormFactory::getElement('xml_desc', 'Label');
		$descElt->setValue(__("Enables you to export an IMS QTI Package."));
		$this->form->addElement($descElt);

		$nameElt = tao_helpers_form_FormFactory::getElement('filename', 'Textbox');
		$nameElt->setDescription(__('File name'));
		$nameElt->setValue($fileName);
		$nameElt->setUnit(".zip");
		$nameElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
    	$this->form->addElement($nameElt);

    	$instanceElt = tao_helpers_form_FormFactory::getElement('instances', 'Checkbox');
    	$instanceElt->setDescription(__('Items'));
    	$instanceElt->setAttribute('checkAll', true);
		$instanceElt->setOptions(tao_helpers_Uri::encodeArray($options, tao_helpers_Uri::ENCODE_ARRAY_KEYS));
    	foreach(array_keys($options) as $value){
			$instanceElt->setValue($value);
		}
		$this->form->addElement($instanceElt);


    	$this->form->createGroup('options', __('Export Options'), array('xml_desc', 'filename', 'instances'));

        // section 127-0-1-1--46051fb4:12ee209629f:-8000:0000000000002D57 end
    }

    /**
     * Short description of method getFormats
     *
     * @access protected
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return array
     */
    protected function getFormats()
    {
        $returnValue = array();

        // section 127-0-1-1--4252657e:1373c83a2a6:-8000:0000000000003A50 begin
        $returnValue = array('rdf' => 'RDF', 'xml' => 'XML', 'imscp' => 'QTI Package');
        // section 127-0-1-1--4252657e:1373c83a2a6:-8000:0000000000003A50 end

        return (array) $returnValue;
    }

} /* end of class taoItems_actions_form_Export */

?>