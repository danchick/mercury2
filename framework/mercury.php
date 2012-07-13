<?php
    /* ***************************************************************************************************
        Mercury Framework v2.0
        www.mercuryframework.com
        Dan Chick - dan@webkit.com
        7/10/2012
    *************************************************************************************************** */

    // set environment 
    include('mercuryRequest.class.php');
    $m = new mercuryRequest();

    // load core functions 
    require('corefunctions.php');

    // check for preconfighooks 
    h("bootstrap/preconfig", $m);    

    // load config 
    require('../site/config/settings.php');
    require('../site/config/database.php');
    require('config/paths.php');

    // check for preprocessing hooks 
    h("bootstrap/preprocess", $m);    
    
    // initialize 
    $m->initialize();

    // figure out what file/action to do 
    $m->determineAction();

    // check for postDetermineAction hooks 
    h("bootstrap/postDetermineAction", $m);
    //$m->hook('bootstrap/postDetermineAction');

    // does it exist in content cache? 
    if($m->inFullContentCache()){ // this function isn't named properly

        // the cache files need to set variables for content other than main **DC
        $m->includeFile();
        
    }else{
        // load global functions if applicable **DC 
        // NOT DONE yet
        
        // include the right file 
        if($m->getProcessVariable('type') == 'webfiles'){

            // if not then include it (setting cache if possible) 
            $m->includeFile();
    
        }else if($m->getProcessVariable('type') == 'module'){

            // run the appropriate function  (setting cache if possible) 
            $m->proceduralAction();

        }else if($m->getProcessVariable('type') == 'object'){

            // run the appropriate function  (setting cache if possible) 
            $m->objectAction();

        }else{
            $m->throw404();
        }
    }

    // check for preRenderPage hooks 
    h("bootstrap/preRenderPage", $m);    
    
    // render page 
    $m->renderPage();

    // check for postRenderPage hooks 
    h("bootstrap/postRenderPage", $m);    
?>