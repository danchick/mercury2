<?php
    class mercurydemo extends mercurydemoClass {

        function postdetail(){
            echo "oo site";
        }

        function postdetail3(){
            echo "oo site 3";
            m("mercurydemo", "printRed", '');
        }
    }
?>