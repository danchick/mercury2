<?php

    class mercuryRequest {
        private $in = array();
        private $process = array();
        private $paths = array();
        private $site = array();
        private $content = array();
        private $header = array();
        private $database = array();
        private $meta = array();
        private $js = array();
        private $css = array();
        private $is_admin_page;
        private $moduledata = array();
	private $interfaceobject;

        ////////////////////////////////////////////////////////////////////////////////////////////////
        
        function __construct($in = array()){
            // initialize $m['in'] with POST and GET arrays
            $this->in = $_POST;
            foreach (array_keys($_GET) as $mercurykeyvalue){
		$this->in[$mercurykeyvalue] = $_GET[$mercurykeyvalue];
            }

            /***** SET SOME DEFAULTS ***********************/
            if(! array_key_exists('LoginId', $_SESSION)) $_SESSION['LoginId'] = 0;
            if(! array_key_exists('Username', $_SESSION)) $_SESSION['Username'] = '';
            if(! array_key_exists('Permissions', $_SESSION)) $_SESSION['Permissions'] = array();
            
            $this->content['main'] = '';
            $this->meta['description'] = '';
            $this->meta['keywords'] = '';
            $this->meta['title'] = '';
            $this->is_admin_page = 0;
            $this->process['include'] = '';
            $this->process['layout'] = 'layout';
            $this->process['layoutpath'] = '';
            $this->process['contentCachePath'] = '';
        }
	
        ////////////////////////////////////////////////////////////////////////////////////////////////
        
        function initialize(){
            // set the timezone based on their settings
            date_default_timezone_set($this->getSiteVariable('TIMEZONE'));
        }
        
        ////////////////////////////////////////////////////////////////////////////////////////////////
	// PROCESS FUNCTIONS
        ////////////////////////////////////////////////////////////////////////////////////////////////

	function proceduralAction(){
	    //include the file the mercury way here
	    ob_start();
	    require_once($this->getProcessVariable('include'));
	    $this->setContentVariable('main', ob_get_contents());
	    ob_end_clean();
        }
        
	function objectAction(){
	    //include the object file
	    foreach($this->getProcessVariable('include') as $includefile){
		require_once($includefile);
	    }

	    // fire up an interface object for that class
	    $modulename = $this->getProcessVariable('instantiate');
	    $this->interfaceobject = new $modulename($this);
	    
	    ob_start();
	    call_user_func(array($this->interfaceobject, $this->getProcessVariable('action')));
	    $this->setContentVariable('main', ob_get_contents());
	    ob_end_clean();
        }
        
        ////////////////////////////////////////////////////////////////////////////////////////////////

        function determineAction(){
            $path = $this->getInVariable('path');
    
            // guard against path traversal
            $path = str_replace("../", "", $path);
            
            // interpret the path
            if($path == ''){
                $this->setModule('');
                $this->setAction('');
            }else{
                $patharray = explode("/", $path);
                
                // check to see if it's admin
                if($path[0] == 'admin'){
                    
                    $this->setIsAdmin = 1;
    
                    // action is the third parameter, if present
                    if (count($patharray) == 1 || $patharray[1] == ""){
                            $this->setModule('admin');
                            $this->setAction('main');
                    }else if (count($patharray) < 3){
                            $this->setModule($patharray[1]);
                            $this->setAction('admin-main');
                    }else{
                            $this->setModule($patharray[1]);
                            $this->setAction($patharray[2]);
                    }
                        
                }else{
                    // give it credit for index if they just passed the module root
                    if (count($patharray) == 1 || (count($patharray)==2 && $patharray[1] == '')){
                            $patharray[1] = 'index';
                    }
                    
                    $this->setModule($patharray[0]);
                    $this->setAction($patharray[1]);
                }            
            }
            
            $slash = $this->getSiteVariable('DIRECTORY_SEPARATOR');
    
            // set paths to search for path translation
            $pathtranslations = array();
            $pathtranslations[] = $this->getPathVariable('SITE_PATHS') . $path;
            $pathtranslations[] = $this->getPathVariable('SITE_PATHS') . $path . $slash . "index.php";
            $pathtranslations[] = $this->getPathVariable('SITE_PATHS') . $path . $slash . "index.html";
            $pathtranslations[] = $this->getPathVariable('SITE_PATHS') . $path . "index.php";
            $pathtranslations[] = $this->getPathVariable('SITE_PATHS') . $path . "index.html";
    
            // pull in an seo file if there is one to be had
            foreach ($pathtranslations as $specificpath){
                if(file_exists($specificpath) && is_file($specificpath)){
                        // seo path will set module, action, cache path and include type **DC
                        require ($specificpath);
                        break;
                }
            }
    
            // set module and action        
            $module = $this->getModule();
            $action = $this->getAction();
    
            // check for the right file to include, webfiles
            $includefiles = array();
            $includefiles[] = $path;
            $includefiles[] = $path . $slash . "index.php";
            $includefiles[] = $path . $slash . "index.html";
            $includefiles[] = $path . "index.php";
            $includefiles[] = $path . "index.html";
            
            foreach ($includefiles as $specificpath){
                //echo "Checking " . $this->getPathVariable('SITE_WEBFILES') . $specificpath . "<BR>";
                if(file_exists($this->getPathVariable('SITE_WEBFILES') . $specificpath) && is_file($this->getPathVariable('SITE_WEBFILES') . $specificpath)){
                    $this->setModule('');
                    $this->setAction('');
                    $this->setProcessVariable('include', $this->getPathVariable('SITE_WEBFILES') . $specificpath);
                    $this->setProcessVariable('type', "webfiles");
                    $this->setProcessVariable('contentCachePath', $specificpath);
                    return;
                }
            }
    
            // check for the right file to include, modules
            $includefiles = array();
	    // procedural files
            $includefiles[] = array('path' =>  $module . $slash . "interface" . $slash . "router.php", 'interface' => 'router');
            $includefiles[] = array('path' =>  $module . $slash . "interface" . $slash . $action . ".php", 'interface' => 'procedural');
            $includefiles[] = array('path' =>  $module . $slash . "interface" . $slash . $action, 'interface' => 'procedural');

            foreach ($includefiles as $pathinfo){
		$specificpath = $pathinfo['path'];
                
                // check the site module path
                if(file_exists($this->getPathVariable('SITE_MODULES') . $specificpath) && is_file($this->getPathVariable('SITE_MODULES') . $specificpath)){
                    $this->setProcessVariable('include', $this->getPathVariable('SITE_MODULES') . $specificpath);
                    $this->setProcessVariable('type', "module");
                    $this->setProcessVariable('contentCachePath', $specificpath);
                    $this->setProcessVariable('interface', $pathinfo['interface']);
                    $this->setProcessVariable('source', 'site');
                    return;
                }
                
                // check the site framework module path
                if(file_exists($this->getPathVariable('FRAMEWORK_MODULES') . $specificpath) && is_file($this->getPathVariable('FRAMEWORK_MODULES') . $specificpath)){
                    $this->setProcessVariable('include', $this->getPathVariable('FRAMEWORK_MODULES') . $specificpath);
                    $this->setProcessVariable('type', "module");
                    $this->setProcessVariable('contentCachePath', $specificpath);
                    $this->setProcessVariable('interface', $pathinfo['interface']);
                    $this->setProcessVariable('source', 'framework');
                    return;
                }
            }        

            // check for the right file to include, modules
            $includefiles = array();
	    // object files
            $includefiles[] = array('path' =>  $module . $slash . $module . ".class.php", 'interface' => 'object');

            foreach ($includefiles as $pathinfo){
		$specificpath = $pathinfo['path'];
		$includes = array();
		
                // check the site framework module path
                if(file_exists($this->getPathVariable('FRAMEWORK_MODULES') . $specificpath) && is_file($this->getPathVariable('FRAMEWORK_MODULES') . $specificpath)){
                    $includes[] = $this->getPathVariable('FRAMEWORK_MODULES') . $specificpath;
                    $this->setProcessVariable('instantiate', $this->getModule());
                }

                // check the site module path
                if(file_exists($this->getPathVariable('SITE_MODULES') . $specificpath) && is_file($this->getPathVariable('SITE_MODULES') . $specificpath)){
                    $includes[] = $this->getPathVariable('SITE_MODULES') . $specificpath;
                    $this->setProcessVariable('instantiate', "_" . $this->getModule());
                }
		
		if(count($includes)){
                    $this->setProcessVariable('include', $includes);
                    $this->setProcessVariable('type', "object");
                    $this->setProcessVariable('contentCachePath', $specificpath);
                    $this->setProcessVariable('interface', $pathinfo['interface']);
		    return;
		}
                
            }        
        }
        
	////////////////////////////////////////////////////////////////////////////////////////////////

        function setProcessArray($processarray){
            $this->process = $processarray;
        }
        function getProcessArray(){
            return $this->process;
        }
        function setProcessVariable($key, $value){
            $this->process[$key] = $value;
        }
        function getProcessVariable($which){
            if(array_key_exists($which, $this->process)){
                return($this->process[$which]);
            }else{
                return '';
            }
        }

        function setAction($which){
            $this->setProcessVariable('action', $which);
        }
        function setModule($which){
            $this->setProcessVariable('module', $which);
        }
        function getAction(){
            return $this->getProcessVariable('action');
        }
        function getModule(){
            return $this->getProcessVariable('module');
        }
	
        ////////////////////////////////////////////////////////////////////////////////////////////////

        function setIsAdmin($which){
            $this->is_admin_page = $which;
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////

        function setLayoutPath($path){
            $this->setProcessVariable('layoutpath', $path);
        }
        
        function getLayoutPath(){
            return $this->getProcessVariable('layoutpath');
        }
        
        ////////////////////////////////////////////////////////////////////////////////////////////////

        function includeFile(){
            ob_start();
            include($this->getProcessVariable('include'));
            $this->setContentVariable('main', ob_get_contents());
            ob_end_clean();
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////
	// CACHE FUNCTIONS
        ////////////////////////////////////////////////////////////////////////////////////////////////
        
        function inFullContentCache(){
            $cachepath = $this->getProcessVariable('contentCachePath');
    
            if($cachepath == ''){
                return(false);
            }
            
            // check to see if there is a cache path
            if(file_exists($this->getPathVariable('SITE_FULLCONTENT_CACHE') . $cachepath)){
                $this->setProcessVariable('include', $this->getPathVariable('SITE_FULLCONTENT_CACHE') . $cachepath);
                $this->setProcessVariable('type', "cache");
                return true;
            }
            
            return false;
        }
        
        ////////////////////////////////////////////////////////////////////////////////////////////////

        function inPartialCache(){
            $cachepath = $this->getProcessVariable('partialCachePath');
    
            if($cachepath == ''){
                return(false);
            }
            
            // check to see if there is a cache path
            if(file_exists($this->getPathVariable('SITE_PARTIALCONTENT_CACHE') . $cachepath)){
                $this->setProcessVariable('include', $this->getPathVariable('SITE_PARTIALCONTENT_CACHE') . $cachepath);
                $this->setProcessVariable('type', "cache");
                return true;
            }
            
            return false;
        }
        

        ////////////////////////////////////////////////////////////////////////////////////////////////
	// VARIABLE AND ATTRIBUTE FUNCTIONS
        ////////////////////////////////////////////////////////////////////////////////////////////////
        
        function setPathArray($patharray){
            $this->paths = $patharray;
        }
        function getPathArray(){
            return $this->paths;
        }
        function getPathVariable($which){
            if(array_key_exists($which, $this->paths)){
                return($this->paths[$which]);
            }else{
                return '';
            }
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////

        function getInArray(){
            return $this->in;
        }
        function getInVariable($which){
            if(array_key_exists($which, $this->in)){
                return($this->in[$which]);
            }else{
                return '';
            }
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////

        function setSiteArray($sitearray){
            $this->site = $sitearray;
        }
        function getSiteArray(){
            return $this->site;
        }
        function getSiteVariable($which){
            if(array_key_exists($which, $this->site)){
                return($this->site[$which]);
            }else{
                return '';
            }
        }
        
        ////////////////////////////////////////////////////////////////////////////////////////////////

        function setDatabaseArray($databasearray){
            $this->database = $databasearray;
        }
        function getDatabaseArray(){
            return $this->database;
        }
        function getDatabaseVariable($which){
            if(array_key_exists($which, $this->database)){
                return($this->database[$which]);
            }else{
                return '';
            }
        }
        
        ////////////////////////////////////////////////////////////////////////////////////////////////

        function setContentArray($contentarray){
            $this->content = $contentarray;
        }
        function getContentArray(){
            return $this->content;
        }
        function setContentVariable($key, $value){
            $this->content[$key] = $value;
        }
        function getContentVariable($which){
            if(array_key_exists($which, $this->content)){
                return($this->content[$which]);
            }else{
                return '';
            }
        }
        
        ////////////////////////////////////////////////////////////////////////////////////////////////

        function setModuleDataArray($module, $moduledataarray){
            $this->moduledata[$module] = $moduledataarray;
        }
        function getModuleDataArray($module){
            return $this->moduledata[$module];
        }
        function setModuleDataVariable($module, $key, $value){
            $this->moduledata[$module][$key] = $value;
        }
        function getModuleDataVariable($module, $which){
            if(array_key_exists($module, $this->moduledata) && array_key_exists($which, $this->moduledata[$module])){
                return($this->moduledata[$module][$which]);
            }else{
                return '';
            }
        }
        
        ////////////////////////////////////////////////////////////////////////////////////////////////
	// RENDER FUNCTIONS
        ////////////////////////////////////////////////////////////////////////////////////////////////
        
        function renderPage(){ // **DC

            // check to see if we need an admin layout
            if($this->is_admin_page == 1){
                if(file_exists( $this->getPathVariable('SITE_LAYOUTS') . "admin.php")){
                    $this->setLayoutPath($this->getPathVariable('SITE_LAYOUTS') . "admin.php");
                } else if(file_exists( $this->getPathVariable('FRAMEWORK_LAYOUTS') . "admin.php")){
                    $this->setLayoutPath($this->getPathVariable('FRAMEWORK_LAYOUTS') . "admin.php");
                } else {
                    echo "Layout: admin.php doesn't exist";
                }
            }else{
                // check to see if the regular layout is actually there
                if(file_exists( $this->getPathVariable('SITE_LAYOUTS') . $this->getProcessVariable('layout') . ".php")){
                    $this->setLayoutPath($this->getPathVariable('SITE_LAYOUTS') . $this->getProcessVariable('layout') . ".php");
                } else {
                    echo "Layout: " . $this->getProcessVariable('layout') . ".php doesn't exist";
                }
            }

            // if there is a layout then use it
            if($this->getLayoutPath() != ''){
                // set the output variable
                ob_start();
                //include a layout
                include ($this->getLayoutPath());
                $page = ob_get_contents();
                ob_end_clean();
            }else{
                // otherwise the output is whatever is in main
                $page = $this->getContentVariable('main');
            }

            // add headers if appropriate
            foreach($this->getHeaderArray() as $value){
                header($value);
            }

            // dump the page
            echo $page;
            
        }
        
        ////////////////////////////////////////////////////////////////////////////////////////////////
	// HEADER FUNCTIONS
        ////////////////////////////////////////////////////////////////////////////////////////////////

        function throw404(){
            $this->setContentVariable('main', '404 not found');
            $this->addHeader('HTTP/1.0 404 Not Found');
        }
        
        ////////////////////////////////////////////////////////////////////////////////////////////////

        function setMimeType($mimetype){
            $this->addHeader("Content-Type: " . $mimetype);
        }
        
        ////////////////////////////////////////////////////////////////////////////////////////////////

        function redirect($url, $statuscode = 301){
            $this->resetHeaders();
            $this->addHeader("HTTP/1.1 ".$statuscode." Moved Permanently");
            $this->addHeader("Location: " . $url);
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////

        function setHeaderArray($headerarray){
            $this->header = $headerarray;
        }
        function getHeaderArray(){
            return $this->header;
        }
        function addHeader($value){
            $this->header[] = $value;
        }
        function resetHeaders(){
            $this->headers = array();
        }
        
    }
?>