<?php
    foreach($this->m->getCSS() as $cssfile){
        ?><link rel="stylesheet" type="text/css" href="<?= $cssfile ?>">
<?php
    }
?>