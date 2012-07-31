<?php
    $this->addCss('/css/site.css');
    css::resetCSS();
    partialMethod('css_showCSS');
?>


<DIV ID="wrapper">
    <DIV ID="header">
        <A HREF="/">Home</A>
        <A HREF="/mercurydemo">Mercury Demo</A>
    </DIV>
    <DIV ID="main">
        <?= $this->getContentVariable('main'); ?>
    </DIV>
    <DIV ID="footer">
        <?= $this->getContentVariable('footer', 'Mercury Framework v2.0.0') ?>
    </DIV>
</DIV>
