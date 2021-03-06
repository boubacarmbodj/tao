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
?>
<?php
/**
 * This controller provide the actions to export and manage exported data
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class tao_actions_Export extends tao_actions_CommonModule {


	/**
	 * to be overriden if needed
	 * @var tao_actions_form_Import
	 */
	protected $formContainer;

	/**
	 * @var array the data to set to the formContainer
	 */
	protected $formData = array();

	/**
	 * Constructor performs initializations actions
	 * @return void
	 */
	public function __construct(){
		parent::__construct();
		$this->formData['currentExtension'] = Context::getInstance()->getExtensionName();
		if($this->hasRequestParameter('classUri')){
			if(trim($this->getRequestParameter('classUri')) != ''){
				$this->formData['class'] = new core_kernel_classes_Class(tao_helpers_Uri::decode($this->getRequestParameter('classUri')));
			}
		}
		if($this->hasRequestParameter('uri') && $this->hasRequestParameter('classUri')){
			if(trim($this->getRequestParameter('uri')) != ''){
				$this->formData['instance'] = new core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
			}
		}
		$this->formContainer = new tao_actions_form_Export($this->formData);
	}

	/**
	 * get the path to save and retrieve the exported files regarding the current extension
	 * @return string the path
	 */
	protected function getExportPath($extension = null){

		$extension = is_null($extension) ? Context::getInstance()->getExtensionName() : $extension;
		$taoExt = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
		$exportPath = $taoExt->getConstant('EXPORT_PATH');

		if(!is_dir($exportPath)){
			common_Logger::i('Export path not found, creating '.$exportPath);
			if(!mkdir($exportPath)){
				throw new common_Exception("Unable to create {$exportPath}. Check your filesystem!");
			}
		}
		return $exportPath;
	}

	/**
	 * The main action.
	 * Display a form to select the export format
	 * @return void
	 */
	public function index(){
		$myForm = $this->formContainer->getForm();
		//if the form is submited and valid
		if ($myForm->isSubmited()) {
			if ($myForm->isValid()) {
				//import method for the given format
				if (!is_null($myForm->getValue('format'))) {
					$exportMethod = 'export'.strtoupper($myForm->getValue('format')).'Data';
					if (method_exists($this, $exportMethod)) {
						//apply the matching method
						$this->$exportMethod($myForm->getValues());
					}
				}
			}
		}
		$this->setData('myForm', $myForm->render());
		$this->setData('formTitle', __('Export'));
		$this->setView('form/export.tpl', 'tao');
	}

	/**
	 * action performed when RDF export form is sent
	 * @param array $formValues the posted data
	 */
	protected function exportRDFData($formValues){
		if(isset($formValues['rdftpl']) && isset($formValues['filename'])){

			$rdf = '';

			//file where we export
			$exportPath = $this->getExportPath();
			$name = $formValues['filename'].'_'.time().'.rdf';
			$path = tao_helpers_File::concat(array($exportPath, $name));
			if(!tao_helpers_File::securityCheck($path, true)){
				throw new Exception('Unauthorized file name');
			}
			common_Logger::i('Exporting to '.$path);

			$api = core_kernel_impl_ApiModelOO::singleton();

			//export by namespace
			if($formValues['rdftpl']['mode'] == 'namespaces'){

				$nsManager = common_ext_NamespaceManager::singleton();

				$namespaces = array();
				foreach($formValues['rdftpl'] as $key => $value){
					if(preg_match("/^ns_/", $key)){
						$modelID = (int)str_replace('ns_', '', $key);
						if($modelID > 0){
							$ns = $nsManager->getNamespace($modelID);
							if($ns instanceof common_ext_Namespace){
								$namespaces[] = (string)$ns;
							}
						}
					}
				}
				if(count($namespaces) > 0){
					$rdf = $api->exportXmlRdf($namespaces);
				}
			}

			//export by instances
			if($formValues['rdftpl']['mode'] == 'instances'){

				$instances = array();
				foreach($formValues['rdftpl'] as $key => $value){
					if(preg_match("/^instance_/", $key)){
						$instances[] = tao_helpers_Uri::decode(str_replace('instance_', '', $key));
					}
				}
				if(count($instances) > 0){
					$xmls = array();
					foreach($instances as $instanceUri){
						$xmls[] = $api->getResourceDescriptionXML($instanceUri);
					}

					if(count($xmls) == 1){
						$rdf = $xmls[0];
					}
					else if(count($xmls) > 1){

						//merge the xml of each instances...

						$baseDom = new DomDocument();
						$baseDom->formatOutput = true;
						$baseDom->loadXML($xmls[0]);

						for($i = 1; $i < count($xmls); $i++){

							$xmlDoc = new SimpleXMLElement($xmls[$i]);
							foreach($xmlDoc->getNamespaces() as $nsName => $nsUri){
								if(!$baseDom->documentElement->hasAttribute('xmlns:'.$nsName)){
									$baseDom->documentElement->setAttribute('xmlns:'.$nsName, $nsUri);
								}
							}
							$newDom = new DOMDocument();
							$newDom->loadXml($xmls[$i]);
							foreach($newDom->getElementsByTagNameNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', "Description") as $desc){
								$newNode = $baseDom->importNode($desc, true);
								$baseDom->documentElement->appendChild($newNode);
							}
						}

						$rdf = $baseDom->saveXml();
					}
				}
			}

			//save it
			if(!empty($rdf)){
				if(file_put_contents($path, $rdf)){
					$this->setData('message', $name.' '.__('exported successfully'));
				}
			}
		}
	}

	/**
	 * Get the list of files exported for the current module
	 * The output is formated to be received by a JS Grid
	 * @return void
	 */
	public function getExportedFiles(){

		$exportPath = $this->getExportPath($this->hasRequestParameter('ext') ? $this->getRequestParameter('ext') : Context::getInstance()->getExtensionName());
		common_Logger::d('Listing exported files from '.$exportPath);
		
		$exportedFiles = array();
		foreach(scandir($exportPath) as $file){
			$path = $exportPath.'/'.$file;
			if(preg_match("/\.(rdf|zip)$/", $file) && !is_dir($path)){
				$exportedFiles[] = array(
					'path'		=> $file,
					'url'		=> str_replace(ROOT_PATH, ROOT_URL.'/', $path),
					'name'		=> substr($file, 0, strrpos($file, '_')),
					'date'		=> date('Y-m-d H:i:s', ((int)substr(preg_replace("/\.(rdf|zip)$/", '', $file), strrpos($file, '_') + 1)))
				);
			}
		}

		$page = $this->getRequestParameter('page');
		$limit = $this->getRequestParameter('rows');
		$sidx = $this->getRequestParameter('sidx');
		$sord = $this->getRequestParameter('sord');
		$start = $limit * $page - $limit;

		if(!$sidx) $sidx =1;

		//slice from start to limit
		$files = array_slice($exportedFiles, $start, $limit);

		$col = array();
		foreach($files as $key => $val){
			$col[$key] = $val[$sidx];
		}
		array_multisort($col, ($sord == 'asc') ? SORT_ASC: SORT_DESC, $files);

		$count = count($exportedFiles);
		if( $count >0 ) {
			$total_pages = ceil($count/$limit);
		}
		else {
			$total_pages = 0;
		}
		if ($page > $total_pages){
			$page = $total_pages;
		}

		$response = new stdClass();
		$response->page = $page;
		$response->total = $total_pages;
		$response->records = $count;
		foreach($files as $i => $file) {
			$response->rows[$i]['id']= $i;
			$response->rows[$i]['cell']= array(
				$file['name'],
				basename($file['path']),
				$file['date'],
				array($file['url'],
					_url('downloadExportedFiles', null, null, array('filePath' => $file['path'])),
					_url('deleteExportedFiles', null, null, array('filePath' => $file['path']))
				)
			);
		}
		echo json_encode($response);
	}

	/**
	 * remove the exported files in parameters
	 * @return void
	 */
	public function deleteExportedFiles(){
		$deleted = false;
		if($this->hasRequestParameter('filePath')){
			$path = $this->getExportPath().DIRECTORY_SEPARATOR.$_GET['filePath'];
			if(tao_helpers_File::securityCheck($path, true)){
				$deleted = tao_helpers_File::remove($path);
			}
		}
		echo json_encode(array('deleted' => $deleted));
	}

	/**
	 * download the exported files in parameters
	 * @return void
	 */
	public function downloadExportedFiles($filePath){

		//get request directly since getRequest changes names
		$path = isset($_GET['filePath']) ? $_GET['filePath'] : '';

		$fullpath = $this->getExportPath().DIRECTORY_SEPARATOR.$path;
		if(tao_helpers_File::securityCheck($fullpath, true) && file_exists($fullpath)){
			$this->setContentHeader(tao_helpers_File::getMimeType($fullpath));
			header('Content-Disposition: attachment; fileName="'.basename($fullpath).'"');
			echo file_get_contents($fullpath);
		}
        else{
            common_Logger::e('Could not find File to export' . $path);
        }

		return;
	}
}
?>
