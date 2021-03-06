<?php

/********************************************************************************/
// autoload objects
/********************************************************************************/
function __autoload($name){
    global $m;
    $parts = explode("_", $name);
    $module = $parts[0];
    if(count($parts) > 1){
        $action = $parts[1];
    }else{
        $action = $module;
    }
    
    // load the base class if it's there
    $slash = $m->getSiteVariable('DIRECTORY_SEPARATOR');
    $basepath = $m->getPathVariable('FRAMEWORK_MODULES'). $module . $slash . $action . ".class.php";
    if(file_exists($basepath)){
        include($basepath);
    }
    
    // load the override or the base implementation
    $classpath = $module . $slash . $action . ".php";

    $sitefile = $m->getPathVariable('SITE_MODULES') . $classpath;
    $frameworkfile = $m->getPathVariable('FRAMEWORK_MODULES') . $classpath;
    
    if(file_exists($sitefile) && is_file($sitefile)){
        include($sitefile);

    }else if(file_exists($frameworkfile) && is_file($frameworkfile)){
        include($frameworkfile);

    }else{
        echo "Could not load class: " . $name;
    }
    
}

/********************************************************************************/
// link
/********************************************************************************/
function l($in){
    
    global $m;
    /*
        link('addRegisterAction')
        link(array('action' =>  'addRegisterAction'))
        link(array('action' =>  'addRegisterAction', 'module' => 'anothermodule'))
        link(array('action' =>  'addRegisterAction', 'module' => 'anothermodule', 'anchor' => "bottom"))
        link(array('action' =>  'addRegisterAction', 'module' => 'anothermodule', 'anchor' => "bottom", 'extrapath' => '4/smurf'))
        link(array('action' =>  'addRegisterAction', 'module' => 'anothermodule', 'anchor' => "bottom", 'extrapath' => '4/smurf', 'querystring' => "a=123&b=red"))
        link(array('anchor' => "bottom", 'extrapath' => '4/smurf', 'querystring' => "a=123&b=red"))
        link(array('type' => "openlink", 'anchor' => "bottom", 'extrapath' => '4/smurf', 'querystring' => "a=123&b=red"))
        link(array('type' => "link", 'anchor' => "bottom", 'extrapath' => '4/smurf', 'querystring' => "a=123&b=red"))
        link(array('linktext' => "click me", 'type' => "link", 'anchor' => "bottom", 'extrapath' => '4/smurf', 'querystring' => "a=123&b=red"))
    */

    if(is_string($in)){
        $in = array('action' => $in);
    }
    
    // default action is blank
    if(! array_key_exists('action', $in)){
        $in['action'] = '';
    }
    
    // default module is current one
    if(! array_key_exists('module', $in)){
        $in['module'] = $m->getModule();
    }
    
    // default type is to return just the url
    if(! array_key_exists('type', $in)){
        $in['type'] = 'urlonly';
    }
    
    // default link text is LINK
    if(! array_key_exists('linktext', $in)){
        $in['linktext'] = 'LINK';
    }
    
    // default target is self
    if(! array_key_exists('target', $in)){
        $in['target'] = '_self';
    }
    if($in['target'] != ''){
        $target = " TARGET='" . $in['target'] . "'";
    }else{
        $target = '';
    }
    
    // default extrapath is blank
    if(! array_key_exists('extrapath', $in)){
        $in['extrapath'] = '';
    }
    if($in['extrapath'] != ''){
        $extrapath = "/" . $in['extrapath'];
        if($in['action'] == ''){
            $in['action'] = "index";
        }
    }else{
        $extrapath = '';
    }
    
    // default querystring is blank
    if(! array_key_exists('querystring', $in)){
        $in['querystring'] = '';
    }
    if($in['querystring'] != ''){
        $querystring = "?" . $in['querystring'];
    }else{
        $querystring = '';
    }
    
    // default querystring is blank
    if(! array_key_exists('anchor', $in)){
        $in['anchor'] = '';
    }
    if($in['anchor'] != ''){
        $anchor = "#" . $in['anchor'];
    }else{
        $anchor = '';
    }
    
    $url = "/" . $in['module'] . "/" . $in['action'] . $extrapath . $anchor . $querystring;
    $openlink = '<A HREF="'.$url.'"'.$target.'>';
    
    //////////////////
    if($in['type'] == "urlonly"){
        return $url;
    }else if($in['type'] == "openlink"){
        return $openlink;
    }else if($in['type'] == "link"){
        return $openlink . $in['linktext'] . "</A>";
    }
    
    return $url;
}

/********************************************************************************/
// partial methods and views
/********************************************************************************/
function partialMethod($call, $in = array(), $contentVariableName = ''){
    $parts = explode("_", $call);
    
    // load the class partial
    $partialpath = $parts[0] . '_partial';
    $partial = new $partialpath;
    call_user_func(array($partial, "router"), array('args' =>  $in, 'action' => $parts[1], 'ContentVariableName' => $contentVariableName));
}

function partialView($module, $action){
    global $m;
    
    if($action == ''){
        $action = $module;
        $module = $this->getModule();
    }
    
    $viewpath = '';
    $possiblepaths = array(
        $m->getPathVariable('SITE_MODULES') . $module .  "/view/partial/" . $action . ".php",
        $m->getPathVariable('FRAMEWORK_MODULES') . $module .  "/view/partial/" . $action . ".php",
    );
    
    foreach($possiblepaths as $path){
        if(file_exists($path)){
            return($path);
        }
    }
    return false;
}

/********************************************************************************/
// object view
/********************************************************************************/
function objectView($module, $object, $view = ''){
    global $m;
    
    if($view == ''){
        $view = $object;
        $object = $module;
        $module = $m->getModule();
    }
    
    $viewpath = '';
    $possiblepaths = array(
        $m->getPathVariable('SITE_MODULES') . $module .  "/view/".$object."/" . $view . ".php",
        $m->getPathVariable('FRAMEWORK_MODULES') . $module .  "/view/".$object."/" . $view . ".php",
    );
    
    foreach($possiblepaths as $path){
        if(file_exists($path)){
            return($path);
        }
    }
    
    die("Couldn't load object view file");
    return false;
}


/********************************************************************************/
// the magic -m-ercury function that will call a function from the site, if defined, and otherwise the core function
/********************************************************************************/
function m($module, $functionname, $variable = array(), $coreonly = 0){
    GLOBAL $m;
    $functionpath = $module . '__'. $functionname;

    if (!function_exists($functionpath)){
        $includepath = "";
        $returnvalue = "";

        $possiblepaths = array();
        if($coreonly == 0){
            $possiblepaths[] = $m->getPathVariable('SITE_MODULES') . $module . '/functions/' . $functionname . '.php';
        }
        $possiblepaths[] = $m->getPathVariable('FRAMEWORK_MODULES') . $module . '/functions/' . $functionname . '.php';
        
        foreach ($possiblepaths as $specificpath){
            if(file_exists($specificpath)){
                $includepath = $specificpath;
                break;
            }
        }
        
        if ($includepath != ''){

            // check to include _all.php if it's there
            $possiblepaths = array();
            if($coreonly == 0){
                $possiblepaths[] = $m->getPathVariable('SITE_MODULES') . $module . '/functions/_all.php';
            }
            $possiblepaths[] = $m->getPathVariable('FRAMEWORK_MODULES') . $module . '/functions/_all.php';
            
            foreach ($possiblepaths as $specificpath){
                if(file_exists($specificpath)){
                    require_once($specificpath);
                    $includepath = $specificpath;
                    break;
                }
            }

            // get the right file
            require_once($includepath);
        }
    }
    
    if (function_exists($functionpath)){
        $returnvalue = call_user_func($functionpath, $variable);
    }else{
        echo 'Did not find path for function '.$functionpath.' in file path: ' . $module . "/functions/" . $functionname . '<BR>Looking in: ';
        dump($possiblepaths);
    }
    
    return($returnvalue);
}

/********************************************************************************/
// functions for dumping arrays
/********************************************************************************/
function arraydump($VARIABLE_ARRAY){
   if (is_array($VARIABLE_ARRAY)) {
      $output = "<table border='1'>";
      foreach ($VARIABLE_ARRAY as $key => $value) {
	    if (is_array($value)) {
			$thevalue = arraydump($value);
			$value = $thevalue;
		}else{
			$thevalue = $value;
			$value = str_replace('<', '&lt;', $value);
		}// end if is array
        $output .= "<tr><td>$key</td><td>$value&nbsp;</td></tr>";
      } // end foreach array value
      $output .= "</table>";
	  return ($output);
   } else {
   	return strval($VARIABLE_ARRAY) . "xxx";
   }
} 

function dump($variablevalue){
	if (is_array($variablevalue)) {
	   	echo arraydump($variablevalue);
	}
}

/********************************************************************************/
// two digit decimal value
/********************************************************************************/
function decimal($val, $precision = 2) {
    if ((float) $val == (int) $val)
	return (int) $val . ".00";
    else if ((float) $val) :
        $val = round((float) $val, (int) $precision);
	if(! strstr($val, ".")){
		$val .= ".";
	}
        list($a, $b) = explode('.', $val);
        if (strlen($b) < $precision) $b = str_pad($b, $precision, '0', STR_PAD_RIGHT);
        return $precision ? "$a.$b" : $a;
    else : // do whatever you want with values that do not have a float
	$val = floatval($val);
        $val = round((float) $val, (int) $precision);
        list($a, $b) = explode('.', $val);
        if (strlen($b) < $precision) $b = str_pad($b, $precision, '0', STR_PAD_RIGHT);
        return $precision ? "$a.$b" : $a;
    endif;
} 

/********************************************************************************/
/********************************************************************************/

function db(){
	require_once(DB);
}



function h($path, $h = array()){
    
    
    return false; // **DC
    
    
	$hookarray = array();
	$possiblepaths = array (
		SITE_FRAMEWORK_PATH . 'hooks/' . $path . "/",
		GLOBAL_FRAMEWORK_PATH . 'hooks/' . $path . "/");
	

	// check each path for includes
	foreach ($possiblepaths as $specificpath){
//		echo $specificpath.BR;
		if(file_exists($specificpath) && is_dir($specificpath)){
//			echo "is dir".BR;
			// include each file that is in the folder
			if ($handle = opendir($specificpath)){
				while (false !== ($file = readdir($handle))){
//					echo "checking $file".BR;
					if (substr($file, 0, 1) !== "." && file_exists(SITE_FRAMEWORK_PATH . preg_replace('/\.[^.]*$/', '', $file) . "/.enabled")
					|| substr($file, 0, 1) !== "." && file_exists(GLOBAL_FRAMEWORK_PATH . preg_replace('/\.[^.]*$/', '', $file) . "/.enabled")){
//						echo "including $file".BR;
						require($specificpath . $file);
					}
				} // end each dir entry
			} // end dir list
		} // end dir exists
	} // end foreach
	return $hookarray;
//	die;
}

function q($sqlstatement){
	db();

	// Perform Query
	$result = mysqli_query($GLOBALS['dbc'], $sqlstatement);
	
	if (!$result) {
		echo "<DIV STYLE=\"border: 1px solid black; padding: 1em;\">";
	    echo '<b>Invalid query:</b><BR> ' . mysqli_error($GLOBALS['dbc']) . "<BR><BR>\n";
	    echo '<b>Whole query:</b><BR> ' . $sqlstatement . "<BR>\n";
		echo "</DIV>";
	    die();
	}
	return $result;
}

function resetQuery(&$query){
	mysqli_data_seek($query, 0);
}


// this is used to load a query into memory rather than just looping it
function qq($result){
	$query = array();
	while ($row = getrow($result)){
		$query[count($query)] = $row;
    }
	return $query;
}

function qd($sqlstatement){
	$result = q($sqlstatement);
	
	echo "<DIV style=\"border: 1px solid #999999; padding: 1em;\">";
	echo "sql: <BR><I>" . $sqlstatement . "</I><BR><BR>";
	echo "rows: " . rowcount($result) . "<BR><BR>";
	qdump($result);
	echo "</DIV>";
	
	return $result;
}

function rowcount($resultset){
	return ( mysqli_num_rows($resultset) );
}

function getrow($resultset){
	return ( mysqli_fetch_array($resultset, MYSQLI_ASSOC) );
}

function qdump($result){
	while ($row = getrow($result)){
		dump($row);
    }
}



function i($module, $path){
    $sitepath = SITE_FRAMEWORK_PATH . $module . '/'.$path.'.php';
    $globalpath = GLOBAL_FRAMEWORK_PATH . $module . '/'.$path.'.php';
    $sitepath2 = SITE_FRAMEWORK_PATH . $module . '/'.$path;
    $globalpath2 = GLOBAL_FRAMEWORK_PATH . $module . '/'.$path;

    if(file_exists($sitepath2) && is_file($sitepath2)){
            return($sitepath2);
    }else if(file_exists($globalpath2) && is_file($globalpath2)){
            return($globalpath2);
    }else if(file_exists($sitepath) && is_file($sitepath)){
            return($sitepath);
    }else{
            return($globalpath);
    }
}

function d($module, $path){
    $sitepath1 = SITE_FRAMEWORK_PATH . $module . '/display/'.$path;
    $sitepath2 = SITE_FRAMEWORK_PATH . $module . '/display/'.$path.'.php';
    $globalpath1 = GLOBAL_FRAMEWORK_PATH . $module . '/display/'.$path;
    $globalpath2 = GLOBAL_FRAMEWORK_PATH . $module . '/display/'.$path.'.php';

    if(file_exists($sitepath1) && is_file($sitepath1)){
            return($sitepath1);
    }else if(file_exists($sitepath2) && is_file($sitepath2)){
            return($sitepath2);
    }else if(file_exists($globalpath1) && is_file($globalpath1)){
            return($globalpath1);
    }else{
            return($globalpath2);
    }
}

function t($module, $type, $path, $default){
    $sitepath1 = SITE_FRAMEWORK_PATH . $module . '/templates/'.$type.'/'.$path.'.php';
    $sitepath2 = SITE_FRAMEWORK_PATH . $module . '/templates/'.$type.'/'.$path;
    $globalpath1 = GLOBAL_FRAMEWORK_PATH . $module . '/templates'.$type.'/'.$path.'.php';
    $globalpath2 = GLOBAL_FRAMEWORK_PATH . $module . '/templates/'.$type.'/'.$path;

    if(file_exists($sitepath1) && is_file($sitepath1)){
            return($sitepath1);
    }else if(file_exists($sitepath2) && is_file($sitepath2)){
            return($sitepath2);
    }else if(file_exists($globalpath1) && is_file($globalpath1)){
            return($globalpath1);
    }else if(file_exists($globalpath2) && is_file($globalpath2)){
            return($globalpath2);
    }else{
            return $default;
    }
}

function hasrows($resultset){
    if (! $resultset){
            return(FALSE);
    }
    
    if (rowcount($resultset) == 0){
            return(FALSE);
    }
    return (TRUE);
}

function redirect($url){
    header("Location: " . $url);
    die();
}

function param(&$arraytopopulate, $arrayofvalues){
    foreach ($arrayofvalues as $key) {
            if(! array_key_exists($key, $arraytopopulate)){
                    $arraytopopulate[$key] = '';
            }
    }
}

function setlayout($layoutpath){
    $GLOBALS['layout'] = $layoutpath;
}

function imagepath($image){
    return ('<IMG SRC="'.RESOURCES_URL.'/images/' . $image . '" BORDER="0">');
}
function successicon($which){
    if($which){
            return (imagepath("check.gif"));
    }else{
            return(imagepath("check_x.gif"));
    }
}

// make an array out of a single incoming variable
function ma($string){
    if(array_key_exists($string, $_POST)){ 
            return (array($_POST[$string]));
    }else if (array_key_exists($string, $_GET)){
            return (array($_GET[$string]));
    }else{
            throw new Exception("$string not defined in GET or POST");
    }
}

// return the last autoincrement
function newid(){
    return ( mysqli_insert_id($GLOBALS['dbc']) );
}

// escape
function e($string){
    db();
    return (mysqli_real_escape_string($GLOBALS['dbc'], $string) );
}

// integer Key value
function k($numberstring){
    return intval($numberstring);
}



// htmlspecialchars or htmlentities
function hh($string){
    return (htmlspecialchars($string));
}

function urlsafe($string){
	
    return u($string);
    
    $que = array( '�','�','�','�','�','�','�','�','�','�','�','�',' ','!','?','/','"',"'","<",">");
    $por = array( 'a','e','i','o','u','A','E','I','O','U','n','n','-','','','','','','','');
    return str_replace( $que,$por,$string );
} 
	
function u($string){
    // convert string to lower
    $string = strtolower($string);
    
    $que = array( '�', '�','�','�','�','�','�','�','�','�','�','�','�',' ','!','?','/','"',"'","<",">");
    $por = array( 'e', 'a','e','i','o','u','A','E','I','O','U','n','n','-','','','','','','','');
    $string = str_replace( $que,$por,$string );
    
    // keep a-z0-9-_.
    $string = preg_replace('/[^a-z0-9\-_\.]/', '-', $string);
    // remove excess - characters
    $string = preg_replace('/\-+/', '-', $string);

    return rawurlencode($string);
}

?>