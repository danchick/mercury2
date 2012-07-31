<?php

    $directory_separator = $m->getSiteVariable('DIRECTORY_SEPARATOR');

    // establish base path
    $paths = array();
    
    $paths['WEB_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $directory_separator;
    $paths['WEB_ROOT_URL'] = "/";
    $paths['WEB_CACHE'] = $paths['WEB_ROOT'] . "cache" . $directory_separator ;
    $paths['WEB_CACHE_URL'] = $paths['WEB_ROOT_URL'] . "cache/" ;
    $paths['WEB_CSS'] = $paths['WEB_ROOT'] . "css" . $directory_separator ;
    $paths['WEB_CSS_URL'] = $paths['WEB_ROOT_URL'] . "css/" ;
    $paths['WEB_IMAGES'] = $paths['WEB_ROOT'] . "images" . $directory_separator ;
    $paths['WEB_IMAGES_URL'] = $paths['WEB_ROOT_URL'] . "images/" ;
    $paths['WEB_SCRIPTS'] = $paths['WEB_ROOT'] . "scripts" . $directory_separator ;
    $paths['WEB_SCRIPTS_URL'] = $paths['WEB_ROOT_URL'] . "scripts/" ;
    $paths['WEB_MERCURY'] = $paths['WEB_ROOT'] . "m" . $directory_separator ;
    $paths['WEB_MERCURY_URL'] = $paths['WEB_ROOT'] . "m/" ;
    $paths['WEB_MERCURY_UPLOADS'] = $paths['WEB_MERCURY'] . "uploads" . $directory_separator ;
    $paths['WEB_MERCURY_UPLOADS_URL'] = $paths['WEB_MERCURY_URL'] . "uploads/" ;
    $paths['WEB_MERCURY_RESOURCES'] = $paths['WEB_ROOT'] . "m/resources/" ;
    $paths['WEB_MERCURY_RESOURCES_URL'] = $paths['WEB_ROOT_URL'] . "m/resources/" ;
    $paths['WEB_MERCURY_ADMIN_RESOURCES'] = $paths['WEB_ROOT'] . "m/resources/admin/" ;
    $paths['WEB_MERCURY_ADMIN_RESOURCES_URL'] = $paths['WEB_ROOT_URL'] . "m/resources/admin/" ;
    
    $paths['ROOT'] = substr($paths['WEB_ROOT'], 0, -4);
    
    $paths['SITE_ROOT'] = $paths['ROOT'] . "site" . $directory_separator;
    $paths['SITE_CONTENT_CACHE'] = $paths['SITE_ROOT'] . "cache" . $directory_separator;
    $paths['SITE_FULLCONTENT_CACHE'] = $paths['SITE_ROOT'] . "cache" . $directory_separator . "fullcontent" . $directory_separator;
    $paths['SITE_PARTIALCONTENT_CACHE'] = $paths['SITE_ROOT'] . "cache" . $directory_separator . "partial" . $directory_separator;
    $paths['SITE_CONFIG_CACHE'] = $paths['SITE_ROOT'] . "cache" . $directory_separator . "config" . $directory_separator;
    $paths['SITE_CONFIG'] = $paths['SITE_ROOT'] . "config" . $directory_separator;
    $paths['SITE_MODULES'] = $paths['SITE_ROOT'] . "modules" . $directory_separator;
    $paths['SITE_LAYOUTS'] = $paths['SITE_ROOT'] . "layouts" . $directory_separator;
    $paths['SITE_PATHS'] = $paths['SITE_ROOT'] . "paths" . $directory_separator;
    $paths['SITE_WEBFILES'] = $paths['SITE_ROOT'] . "webfiles" . $directory_separator;
    
    $paths['FRAMEWORK_ROOT'] = $paths['ROOT'] . "framework" . $directory_separator;
    $paths['FRAMEWORK_CONFIG'] = $paths['FRAMEWORK_ROOT'] . "config" . $directory_separator;
    $paths['FRAMEWORK_LAYOUTS'] = $paths['FRAMEWORK_ROOT'] . "layouts" . $directory_separator;
    $paths['FRAMEWORK_MODULES'] = $paths['FRAMEWORK_ROOT'] . "modules" . $directory_separator;
    
    $paths['WEB_RESOURCES'] = $paths['FRAMEWORK_ROOT'] . "mercuryresources" . $directory_separator ;
    $paths['WEB_RESOURCES_URL'] = $paths['WEB_ROOT_URL'] . "mercuryresources/" ;
    
    $m->setPathArray($paths);

?>