<?php
    foreach($this->m->getJavascript() as $jsfile){
        ?><script type="application/x-javascript" src="<?= $jsfile ?>"></script>        
<?php
    }
?>