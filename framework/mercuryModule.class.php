<?php
    class mercuryModule {
	
	protected $m;
	protected $moduleName;
	protected $parts;

        ////////////////////////////////////////////////////////////////////////////////////////////////
        
	// every object gets access to the $m global as $this->m
        final function __construct($in = array()){
	    global $m;
	    $this->m = $m;
	    $this->_construct($in);
	    $parts = explode("_", get_class($this));
	    $this->moduleName = $parts[0];
	    $this->parts = $parts;
        }
	
        ////////////////////////////////////////////////////////////////////////////////////////////////
        
	// this method should be overridden on any module that inherits this one
        function _construct($in = array()){
	    
        }
	
        ////////////////////////////////////////////////////////////////////////////////////////////////
        function _before($in = array()){
	    
        }
	
        ////////////////////////////////////////////////////////////////////////////////////////////////
        function _after($in = array()){
	    
        }
	
        ////////////////////////////////////////////////////////////////////////////////////////////////

	// call the appropriate method        
        function router($in){

	    // determine what is being passed
	    if(is_a($this, 'mercuryPartial') ){
		$pass = $in['args'];
		$action = $in['action'];
	    }else{
		$pass = $this->m;
		$action = $in;
		$this->m->setView($action);
	    }
	    
	    $this->_before($in);
	    
	    // if the method exists, call it
	    if(method_exists($this, $action)){

		// call the action
		call_user_func(array($this, $action), $pass);

	    	// default view for this action is the same name as the action
		// this can change during the processing
		if(is_a($this, 'mercuryPartial') ){
		    if($viewpath = partialView($this->moduleName, $action)){
			if(array_key_exists('ContentVariableName', $in) && $in['ContentVariableName'] != ''){
			    ob_start();
			    include($viewpath);
			    $content = ob_get_contents();
			    ob_end_clean();
			    $this->m->setContentVariable($in['ContentVariableName'], $content);
			}else{
			    include($viewpath);
			}
		    }
		}else{
		    if($viewpath = $this->m->getViewPath()){
			include ($viewpath);
		    }else{
			echo "View not found for module: ". $this->moduleName .", action: " . $action ;
		    }
		}

		
	    // if the method doesn't exist, bail
	    }else{
		echo "Method '" .$action. "' doesn't exist in module '" . $this->m->getModule() ."'";
	    }

	    $this->_after($in);
	    
        }
    }
?>