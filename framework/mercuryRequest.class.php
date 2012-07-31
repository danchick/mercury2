<?php

    class mercuryRequest {
        private $in = array();
        private $process = array();
        private $paths = array();
        private $site = array();
        private $content = array();
        private $breadcrumbs = array();
        private $header = array();
        private $database = array();
        protected $meta = array();
        protected $js = array();
        protected $css = array();
	protected $bodyclass = array();
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
                if($patharray[0] == 'admin'){
		    
                    $this->setIsAdmin(1);
    
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
    
            // pull in a path translation file if there is one to be had
            foreach ($pathtranslations as $specificpath){
                if(file_exists($specificpath) && is_file($specificpath)){
                        // path translation path will set module, action, cache path and include type **DC
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
                    $this->setProcessVariable('type', "procedural");
                    $this->setProcessVariable('contentCachePath', $specificpath);
                    $this->setProcessVariable('interface', $pathinfo['interface']);
                    $this->setProcessVariable('source', 'site');
                    return;
                }
                
                // check the site framework module path
                if(file_exists($this->getPathVariable('FRAMEWORK_MODULES') . $specificpath) && is_file($this->getPathVariable('FRAMEWORK_MODULES') . $specificpath)){
                    $this->setProcessVariable('include', $this->getPathVariable('FRAMEWORK_MODULES') . $specificpath);
                    $this->setProcessVariable('type', "procedural");
                    $this->setProcessVariable('contentCachePath', $specificpath);
                    $this->setProcessVariable('interface', $pathinfo['interface']);
                    $this->setProcessVariable('source', 'framework');
                    return;
                }
            }        

            // check for the right file to include, modules
	    $baseClass = $this->getPathVariable('FRAMEWORK_MODULES') . $module . $slash . $module . ".class.php";
            $includefiles = array(
				    $this->getPathVariable('SITE_MODULES') . $module . $slash . $module . ".php",
				    $this->getPathVariable('FRAMEWORK_MODULES') . $module . $slash . $module . ".php"
				    );
	    
            foreach ($includefiles as $specificpath){
                // check the site framework module path
                if(file_exists($specificpath) && is_file($specificpath)){
		    // load the base class and implementation if it's core, otherwise load as is
		    if($specificpath == $this->getPathVariable('FRAMEWORK_MODULES') . $module . $slash . $module . ".php"){
			$includes = array($baseClass, $specificpath);
		    }else{
			$includes = array();
		    }
		    // set the processing flags
                    $this->setProcessVariable('instantiate', $this->getModule());
		    $this->setProcessVariable('include', $includes);
		    $this->setProcessVariable('type', "object");
		    $this->setProcessVariable('contentCachePath', $specificpath);
		    $this->setProcessVariable('interface', 'method');
		    return;
                }
            }        
        }

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
	    call_user_func(array($this->interfaceobject, 'router'), $this->getAction());
	    $this->setContentVariable('main', ob_get_contents());
	    ob_end_clean();
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
        function setView($which){
            $this->setProcessVariable('view', $which);
        }
        function setLayout($which){
            $this->setProcessVariable('layout', $which);
        }
        function getLayout(){
            return $this->getProcessVariable('layout');
        }
        function getAction(){
            return $this->getProcessVariable('action');
        }
        function getModule(){
            return $this->getProcessVariable('module');
        }
        function getView(){
            return $this->getProcessVariable('view');
        }
	
        ////////////////////////////////////////////////////////////////////////////////////////////////

        function getViewPath(){
	    
	    $viewpath = '';
	    $possiblepaths = array(
		$this->getPathVariable('SITE_MODULES') . $this->getModule() .  "/view/" . $this->getView() . ".php",
		$this->getPathVariable('FRAMEWORK_MODULES') . $this->getModule() .  "/view/" . $this->getView() . ".php",
	    );
	    
	    foreach($possiblepaths as $path){
		if(file_exists($path)){
		    return $path;
		}
	    }
	    
	    return false;
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
        function getContentVariable($which, $default = ''){
            if(array_key_exists($which, $this->content)){
                return($this->content[$which]);
            }else{
                return $default;
            }
        }
        
        ////////////////////////////////////////////////////////////////////////////////////////////////

        function addBreadcrumb($text, $url, $position = 'last'){
	    $crumb = array('url' =>  $url, 'text' => $text);
	    if($position == "first"){
		array_unshift($this->breadcrumbs, $crumb);
	    }
	    if($position == "last"){
		$this->breadcrumbs[] = $crumb;
	    }	
        }
        function getBreadcrumbs(){
            return($this->breadcrumbs);
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
        
        function renderPage(){

            // check to see if we need an admin layout
            if($this->is_admin_page == 1){
		$this->setAdminResources();

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
                } else if($this->getProcessVariable('layout') != ''){
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
	
	function setAdminResources(){
	    // if there is an admin.css file in the web css folder use it
	    if(file_exists($this->getPathVariable('WEB_CSS') . "admin.css")){
		$this->addCSS($this->getPathVariable('WEB_CSS_URL') . "admin.css");

	    // otherwise use the one that is in the m/resources folder
	    }else{
		$this->addCSS($this->getPathVariable('WEB_MERCURY_ADMIN_RESOURCES_URL') . "admin.css");
	    }
	    
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
        ////////////////////////////////////////////////////////////////////////////////////////////////
	// PAGE PIECES
        ////////////////////////////////////////////////////////////////////////////////////////////////
        function addCSS($path, $position = "last", $pathkey = ''){
	    if($pathkey != ''){
		$path = $this->getPathVariable($pathkey) . $path;
	    }

	    $i = array_search($path, $this->getCSS());
	    if($i === FALSE){
		if($position == "first"){
		    array_unshift($this->css, $path);
		}
		if($position == "last"){
		    $this->css[] = $path;
		}	
	    }
	}
	function getCSS(){
	    return $this->css;
	}
	function printCSS(){
	    partial("core", "printCSS", array('css' =>  $this->getCSS()));
	}

        function addJavascript($path, $position = "last"){
	    if($position == "first"){
		array_unshift($this->js, $path);
	    }
	    if($position == "last"){
		$this->js[] = $path;
	    }
	}
	function getJavascript(){
	    return $this->js;
	}
        function addBodyClass($classname, $position = 'last'){
	    if($position == "first"){
		array_unshift($this->bodyclass, $classname);
	    }
	    if($position == "last"){
		$this->bodyclass[] = $classname;
	    }
	}
	function getBodyClass(){
	    $class="";
	    foreach($this->bodyclass as $bodyclass){
		$class = $class . " " . $bodyclass;
	    }
	    return $class;
	}
	function getBodyClassArray(){
	    return $this->bodyclass;
	}

	function addPageMessage($message, $type = "info"){
	    if(! array_key_exists('PageMessages', $_SESSION)){
		$_SESSION['PageMessages'] = array();
	    }
	    if(! is_array($_SESSION['PageMessages'])){
		$_SESSION['PageMessages'] = array();
 	    }
	    // error, success, info
	    $message = array('Message' => $message, 'Type' => $type);
	    $_SESSION['PageMessages'][] = $message;
	}
	function getPageMessages(){
	    
	    if(array_key_exists('PageMessages', $_SESSION) && is_array($_SESSION['PageMessages']) && count($_SESSION['PageMessages'])){
		$messages = $_SESSION['PageMessages'];
		unset($_SESSION['PageMessages']);
		return($messages);
	    }else{
		unset($_SESSION['PageMessages']);
		return array();
	    }
	}
	function showPageMessage(){
	    $messages = $this->getPageMessages();
	    foreach($messages as $message){
		if($message['Message']){
		    ?>
			<div class="alert alert-<?= hh($message['Type']) ?>">
			    <button type="button" class="close" data-dismiss="alert">&times;</button>
			    <?= hh($message['Message']) ?>
			</div>
		    <?php
		}	
	    }
	}

    }
?>