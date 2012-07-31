<?php
    $this->addCss('/css/site.css', "last");
    css::resetCSS();
    partialMethod('css_showCSS');
?>


<DIV ID="wrapper">
    <DIV ID="header">
        header
    </DIV>
    <DIV ID="main">
        <?= $this->getContentVariable('main'); ?>
    </DIV>
    <DIV ID="footer">
        footer
    </DIV>
</DIV>
