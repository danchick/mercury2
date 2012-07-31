<?php
    class cssClass extends mercuryModule {

        function _construct(){
        }

        function resetCSS(){
            $this->addCSS("css/reset.css", 'first', 'WEB_MERCURY_RESOURCES_URL');
        }
    }
?>