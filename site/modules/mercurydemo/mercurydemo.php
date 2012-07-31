<?php
    class mercurydemo extends mercurydemoClass {

        function postdetail(){
            echo "oo site";
            $this->widget = new mercurydemo_widget(5);
        }

        function postdetail3(){
            echo "oo site 3";
            m("mercurydemo", "printRed", '');
        }
    }
?>