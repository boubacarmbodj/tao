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
 * The precompilator helper provides methods for the delivery compilation action
 * such as file copy, error management or file parser
 *
 * @access public
 * @author CRP Henri Tudor - TAO Team - {@link http://www.taotesting.com}
 * @package taoDelivery
 * @subpackage helpers
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
class taoDelivery_helpers_Compilator
{
	/**
     * The attribute "completed" contains the array of completed actions performed during the delivery compilation
	 * (e.g. file copy, file or folder creation) 
     *
     * @access protected
     * @var array
     */
	protected $completed = array();
	
	/**
     * The attribute "failed" contains the array of failed actions performed during the delivery compilation
	 * (e.g. file copy, file or folder creation) 
     *
     * @access protected
     * @var array
     */
	protected $failed = array();
	
	/**
     * The attribute "pluginPath" define the directory where all required runtime plugins are stored
     *
     * @access protected
     * @var string
     */
	protected $pluginPath = '';
	
	/**
     * The attribute "compiledPath" define the directory where all compiled files for the test will be stored
     *
     * @access public
     * @var string
     */
	protected $compiledPath = '';
	
	/**
     * The attribute "item" define the item that is being compiled
     *
     * @access protected
     * @var core_kernel_classes_Resource
     */
	protected $item = null;
	
	/**
     * The attribute "test" define the test that is being compiled
     *
     * @access protected
     * @var core_kernel_classes_Resource
     */
	protected $test = null;
	
	/**
     * The attribute "delivery" define the delivery that is being compiled
     *
     * @access protected
     * @var core_kernel_classes_Resource
     */
	protected $delivery = null;
	
	
	/**
     * The method __construct intiates the Precompilator class by setting the initial values to the attributes 
     *
     * @access public
     * @author CRP Henri Tudor - TAO Team - {@link http://www.taotesting.com}
	 * @param  string delivery
	 * @param  string test
	 * @param  string item
	 * @param  string compiledPath
	 * @throws taoDelivery_helpers_CompilationException
     */	
	public function __construct(core_kernel_classes_Resource $delivery, core_kernel_classes_Resource $test, core_kernel_classes_Resource $item, $compiledPath=''){
		
		$this->delivery = $delivery;
		$this->test = $test;
		$this->item = $item;
		
		$this->completed = array(
			"copiedFiles"=>array(),
			"createdFiles"=>array()
		);
					
		$this->failed = array(
			"copiedFiles"=>array(),
			"createdFiles"=>array(),
			"untranslatedItems"=>array(),
			"errorMsg"=>array()
		);
		
		
		$deliveryExtension = common_ext_ExtensionsManager::singleton()->getExtensionById('taoDelivery');
		if(!empty($pluginPath)){
			$this->pluginPath = $pluginPath;
		}else{
			$this->pluginPath = $deliveryExtension->getConstant('BASE_PATH')."/lib/";
		}
		if(!is_dir($this->pluginPath)){
			throw new taoDelivery_helpers_CompilationException("The plugin directory '{$this->pluginPath}' does not exist", $this->item);
		}
		
		if(!empty($compiledPath)){
			$this->compiledPath = $compiledPath;
		}else{
			$this->compiledPath = $deliveryExtension->getConstant('BASE_PATH')."/compiled/";
		}
		if(!is_writable($this->compiledPath)){
			throw new taoDelivery_helpers_CompilationException("The compiled directory '{$this->compiledPath}' is not writable", $this->item);
		}
			
		if(!is_dir($this->compiledPath)){
			$this->failed["createdFiles"]["compiled_test_folder"] = $this->compiledPath;
			throw new taoDelivery_helpers_CompilationException("The main compiled test directory '{$this->compiledPath}' does not exist", $this->item);
		}
	}
	
	/**
     * Returns the compilation path of the compilator
     *
     * @access public
     * @author CRP Henri Tudor - TAO Team - {@link http://www.taotesting.com}
     * @return string
     */	
	public function getCompiledPath(){
		return $this->compiledPath;
	}
	
	/**
     * The method copyFile enable tje conpilator to copy a file from a remote location
	 * Depending on the success or the failure of the operation, it records the result either in the class attribute "completed" or "failed"
     * If the copy succeeds, it returns the name and the extension of the copied file, with the format "name.extension". 
     * It returns an empty string otherwise.
	 *
	 * @access public
     * @author CRP Henri Tudor - TAO Team - {@link http://www.taotesting.com}
	 * @param  string url
	 * @param  string directory
	 * @param  string affectedObject
     * @return string
     * @throws taoDelivery_helpers_CompilationException
     */		
	public function copyFile($url, $directory="", $affectedObject="", $rename=false){
	
		$returnValue = "";
		
		if (empty($directory)){
			$directory=$this->compiledPath;
		}
		
		if (empty($affectedObject)){
			$affectedObject="undefinedObject";
		}
		
		$fileName = basename($url);
			
		//check whether the file has been already downloaded: e.g. in the case an item existing in several languages share the same multimedia file
		$isCopied=false;
		foreach ($this->completed["copiedFiles"] as $copiedFiles){
			//Check if it has not been copied yet
			if(in_array($url, $copiedFiles)) {
				$isCopied=true;
				return $fileName;
			}
		}
		
		if($isCopied === false){
			// Since the file has not been downloaded yet, start downloading it using cUrl

			// Only if the resource is external to TAO or in the filemanager of the current instance.
			error_reporting(E_ALL);
			if(!preg_match('@^' . BASE_URL . '@', $url)){
				
				$curlHandler = curl_init();
				curl_setopt($curlHandler, CURLOPT_URL, $url);
				
				//if there is an http auth on the local domain, it's mandatory to auth with curl
				if(USE_HTTP_AUTH){	
					$addAuth = false;
					$domains = array('localhost', '127.0.0.1', ROOT_URL);
					foreach($domains as $domain){
						if(preg_match("/".preg_quote($domain, '/')."/", $url)){
							$addAuth = true;
						}
					}
					if($addAuth){
						curl_setopt($curlHandler, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
						curl_setopt($curlHandler, CURLOPT_USERPWD, USE_HTTP_USER.":".USE_HTTP_PASS);
					}
				}
				curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);
				
				$fileContent = curl_exec($curlHandler);
				
				curl_close($curlHandler);  
			}
			else{
			
				// Duplicated file copy. Not useful.
				//$fileContent = @file_get_contents($url);
				$fileContent = null;
			}
			
			if ($fileContent === false){
				
				$this->failed["copiedFiles"][$affectedObject][]=$url;
				return $returnValue;
			}
						
			//check file name compatibility: 
			//e.g. if a file with a common name (e.g. car.jpg, house.png, sound.mp3) already exists in the destination folder
			while(file_exists($directory.$fileName) && $rename===true){
				$reverseFileName = strrev($fileName); 
				$reverseExt = substr($reverseFileName, 0, strpos($reverseFileName,"."));
				$reverseName = substr($reverseFileName, strpos($reverseFileName,".")+1);
				
				//add an underscore so it becomes unique
				$fileName = strrev($reverseName)."_.".strrev($reverseExt);
			}
			
			if($fileContent !== null && file_put_contents($directory.$fileName, $fileContent) === false){
				throw new taoDelivery_helpers_CompilationException("The file '$directory.$fileName' cannot be written.", $this->item);
			}
			
			//record in the property "completed" that the file has been successfullly downloaded 
			if ($fileContent !== null){
				$this->completed["copiedFiles"][$affectedObject][]=$url;
				$returnValue = $fileName;
			}
			else {
				$returnValue = false;	
			}
			
			
		}
				
		return $returnValue;
	}
    
	/**
	 * @todo : get the plugins to be copied in thte compiled delivery according to the item type
	 * @access public
     * @author CRP Henri Tudor - TAO Team - {@link http://www.taotesting.com}
     * @return array
     * @throws taoDelivery_helpers_CompilationException
	 */
	public function getPlugins(){
		
		$returnValue = array();
		
		//@todo : get the plugins to be copied in thte compiled delivery according to the item type
		//@todo : distinguish language dependent and non-dependent resources?
		$itemModel = taoItems_models_classes_ItemsService::singleton()->getItemModel($this->item);
		$extManager = common_ext_ExtensionsManager::singleton();
		$libs = array();
		
		if ($itemModel->getUri() == TAO_ITEM_MODEL_QTI){
			$taoQTIext = $extManager->getExtensionById('taoQTI');
			
			if ($taoQTIext !== null){
				$libs['QtiImg'] 		= 	array('path' => $taoQTIext->getConstant('BASE_PATH') . 'views/js/QTI/img/',
										 		   'relativePath' => '../img/',
										 		   'files' => '*');
				$libs['QtiJqueryUIimg'] = 	array('path' => TAOVIEW_PATH . 'css/custom-theme/images/',
												   'relativePath' => 'images/',
												   'files' => '*');
			}
		}
		else if ($itemModel->getUri() == TAO_ITEM_MODEL_XHTML){
			// I am so sorry for this guys. (JBO)
			$apis = taoItems_models_classes_XHTML_Service::buildApisArray();
			
			try{
				$dom = new DOMDocument('1.0', TAO_DEFAULT_ENCODING);
				if (!$dom->loadHTML(file_get_contents($this->compiledPath . DIRECTORY_SEPARATOR . 'index.html'))){
					
					$itemUri = $this->item->getUri();
					$msg = "An error occured while loading the XHTML content of item '${itemUri}'.";
					throw new taoDelivery_helpers_CompilationException($msg, $this->item);
				}
				else{

					foreach ($apis as $pattern => $infos){
						$elements = taoItems_helpers_Xhtml::getScriptElements($dom, '/' . $pattern . '/i');
						
						foreach ($elements as $e){
							
							if ($e->getAttribute('src') == $infos['src']){
								$libs['Xhtml_' . $pattern] = array('path' => pathinfo($infos['path'], PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR,
																	'relativePath' => 'tao-apis/',
																	'files' => array($pattern . 'min.js'));
								
								common_Logger::d("'${pattern}' JavaScript API will be copied in the item.");
							}
							break;
						}
					}
				}
			}
			catch (DOMException $e){
				$itemUri = $this->item->getUri();
				$msg = "An error occured while parsing the XHTML content of item '${itemUri}'.";
				throw new taoDelivery_helpers_CompilationException($msg, $this->item);
			}
			catch (taoDelivery_helpers_CompilationException $e){
				throw $e;
			}
		}
		
		// Copy plugins.
		foreach ($libs as $libConf) {
			if (isset($libConf['path']) && isset($libConf['relativePath']) && isset($libConf['files'])) {
				$path = $libConf['path'];
				$relativePath = $libConf['relativePath'];
				$files = $libConf['files'];
				if ($files === '*') {
					foreach (scandir($path) as $fileName) {
						if (is_file($path . $fileName)) {
							$returnValue[$path . $fileName] = $relativePath . $fileName;
						}
					}
				} elseif (is_array($files)) {
					foreach ($files as $fileName) {
						if (is_file($path . $fileName)) {
							$returnValue[$path . $fileName] = $relativePath . $fileName;
						}
					}
				}
			}
		}
		
		return $returnValue;
	}
	
	/**
     * The method copyFile firstly defines the runtime files to be included in each compiled test folder
	 * Then it calls the copyFile method to accomplish its task
	 *
	 * @access public
     * @author CRP Henri Tudor - TAO Team - {@link http://www.taotesting.com}
     */
	public function copyPlugins(){
		foreach ($this->getPlugins() as $absoluePath => $relativePath){
			if(tao_helpers_File::copy($absoluePath, $this->compiledPath.'/'.$relativePath, true)){
				$this->completed['copiedFiles'][] = $absoluePath;
			}else{
				$this->failed['copiedFiles'][] = $absoluePath;
			}
		}
	}
	
	/**
     * The method itemParser parses the ItemContent xml file and executes fileCOpy with media to be downloaded.
	 * It also replaces the old link to the media file with the new ones in the ItemContent XML file and returns it as a string.
	 *
	 * @access public
     * @author CRP Henri Tudor - TAO Team - {@link http://www.taotesting.com}
	 * @param  string xml
	 * @param  string directory
	 * @param  string itemName
	 * @param  array authorizedMedia
     * @return string
     * @throws taoDelivery_helpers_CompilationException
     */	
	public function itemParser($xml, $directory, $itemName, $authorizedMedia=array()){
		
		if(!file_exists($directory)){
			throw new taoDelivery_helpers_CompilationException("The specified directory '${directory}' does not exist.", $this->item);
		}
		
		$defaultMedia = array("jpg","jpeg","png","gif","mp3",'swf','wma','wav', 'css', 'js');
		
		$authorizedMedia = array_merge($defaultMedia, $authorizedMedia);
		$authorizedMedia = array_unique($authorizedMedia);//eliminate duplicate
		
		$mediaList = array();
		$expr = "/http[s]?:\/\/[^<'\"&?]+\.(".implode('|',$authorizedMedia).")/mi";
		preg_match_all($expr, $xml, $mediaList, PREG_PATTERN_ORDER);

		$plugins = $this->getPlugins();
		$uniqueMediaList = 	array_unique($mediaList[0]);
		$compiledUrl = tao_helpers_Uri::getUrlForPath($this->compiledPath);
		
		foreach($uniqueMediaList as $mediaUrl){
			if(in_array(basename($mediaUrl), $plugins)){
				//if it is only a (valid) plugin file, don't try to download it but simply change the link:
				//if the user upload an OWI with the exact same name and path, consider it as the same as the TAO version
				if(preg_match_all('/\.(js|css|swf)$/i', basename($mediaUrl), $matches)){
					// This break paths ! to change in further versions.	
					//$xml = str_replace($mediaUrl, $compiledUrl.'/'.$matches[1][0].'/'.basename($mediaUrl), $xml, $replaced);
				}
			}
			else{
				// This is a file that has to be stored in the item compilation folder itself...
				// I do not get why they are all copied. They are all there they were copied from the item module...
				// But I agree that remote resources (somewhere on the Internet) should be copied via curl.
				// So if the URL does not matches a place where the TAO server is, we curl the resource and store it.
				// FileManager files should be considered as remote resources to avoid 404 issues. Indeed, a backoffice
				// user might delete an image in the filemanager during a delivery campain. This is dangerous.
				$mediaPath = $this->copyFile($mediaUrl, $directory.'/', $itemName, true);
				if(!empty($mediaPath) && $mediaPath !== false){
					$xml = str_replace($mediaUrl, $compiledUrl.'/'.basename($mediaUrl), $xml, $replaced);//replace only when copyFile is successful
				}
			}
		}
		return $xml;
	}
	
	/**
	 * The method stringToFile is used to write the required test and item XML files in the local disk.
	 * It also manages errors and exceptions of the operation by recording the result in the class attributes "completed" or "failed"
	 *
	 * @access public
     * @author CRP Henri Tudor - TAO Team - {@link http://www.taotesting.com}
	 * @param  string content
	 * @param  string directory
	 * @param  string fileName
	 * @throws taoDelivery_helpers_CompilationException
     */	
	public function stringToFile($content, $directory, $fileName){
		if (!is_dir($directory)){
			$created = mkdir($directory);
			if ($created === false){
				$this->failed["createdFiles"][$directory]=$fileName;
				throw new taoDelivery_helpers_CompilationException("The folder '${directory}' does not exist and can not be created.", $this->item);
			}
		}
		$handle = fopen($directory . '/' . $fileName,'wb');
		$content = fwrite($handle, $content);
		fclose($handle);
		$this->completed["createdFiles"][] = $fileName;
	}
	
	/**
	 * The method result returns the protected attributes "completed" and "failed" 
	 *
	 * @access public
     * @author CRP Henri Tudor - TAO Team - {@link http://www.taotesting.com}
     * @return array
     */	
	public function result(){
		$returnValue = array("completed"=>$this->completed, "failed"=>$this->failed);
		return $returnValue;
	}

	/** 
	* Get the default absolute path to the compiled folder of a test 
	*/
	public static function getCompiledTestUrl($deliveryUri, $testUri, $itemUri){
		$testUrl ='';
		
		$testUniqueId = tao_helpers_Uri::getUniqueId($testUri);
		if(!empty($testUniqueId)){
			$testUrl = tao_helpers_Uri::getUniqueId($deliveryUri).'/'.tao_helpers_Uri::getUniqueId($testUri).'/'.tao_helpers_Uri::getUniqueId($itemUri).'/index.html';
		}
		
		return $testUrl;
	}
	
	/**
	 * Prepares the compiled folder
	 *
	 * @access public
     * @author CRP Henri Tudor - TAO Team - {@link http://www.taotesting.com}
     * @return boolean
     * @throws common_exception_Error If the compiled path is not secure.
     */	
	public function prepareCompileFolder(){
		$returnValue = false;
		
		$path = $this->compiledPath;
		if (!tao_helpers_File::securityCheck($path, true)) {
			throw new common_exception_Error("Forbidden path format for '${path}'.");
		}
		
		if (file_exists($this->compiledPath)) {
			helpers_File::remove($this->compiledPath);
		}
		
		mkdir($this->compiledPath);
		
		return $returnValue;
	}

	
	/**
	* record the items that have translation issues in the "failed" array
	* @access protected
	* @param string $name
	* @param string $language
	* @author CRP Henri Tudor - TAO Team - {@link http://www.taotesting.com}
	*/
	public function setUntranslatedItem($name, $language){
		$this->failed["untranslatedItems"][$language][] = $name;
	}
	
	/**
	* record an error message in the "errorMsg" array
	* @access protected
	* @param string $message
	* @author CRP Henri Tudor - TAO Team - {@link http://www.taotesting.com}
	*/
	public function setErrorMsg($message){
		$this->failed["errorMsg"][] = $message; 
	}
	
}

?>