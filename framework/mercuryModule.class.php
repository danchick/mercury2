<?php
    class mercuryModule {
	
	protected $m;
	protected $moduleName;

        ////////////////////////////////////////////////////////////////////////////////////////////////
        
	// every object gets access to the $m global as $this->m
        final function __construct($in = array()){
	    global $m;
	    $this->m = $m;
	    $this->_construct($in);
	    $parts = explode("_", get_class($this));
	    $this->moduleName = $parts[0];
        }
	
        ////////////////////////////////////////////////////////////////////////////////////////////////
        
	// this method should be overridden on any module that inherits this one
        function _construct($in = array()){
	    
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
	    
	    // if the method exists, call it
	    if(method_exists($this, $action)){

	    	// default view for this action is the same name as the action
		// this can change during the processing
		
		// call the action
		call_user_func(array($this, $action), $pass);

		// see if the view file is there
		if(is_a($this, 'mercuryPartial') ){
		    if($viewpath = partialView($this->moduleName, $action)){
			include($viewpath);
		    }
		}else{
		    if($viewpath = $this->m->getViewPath()){
			include ($viewpath);
		    }else{
			echo "View not found";
		    }
		}
		
	    // if the method doesn't exist, bail
	    }else{
		echo "Method '" .$action. "' doesn't exist in module '" . $this->m->getModule() ."'";
	    }
	    
        }
    }
?>