<?php
//    class blog extends mercury {
    class blog {
        private $m;

        function __construct(&$m){
            $this->m = $m;
        }

        function postdetail(){
            echo "oo framework";
        }
        function postdetail2(){
            echo "oo framework 2";
        }
    }
?>