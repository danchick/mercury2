<?php
    $database = array();

    $database['DB_HOST'] = "localhost";
    $database['DB_NAME'] = "mentalswitch";
    $database['DB_USER'] = "root";
    $database['DB_PASSWORD'] = "mypassword";
//    $database['CONNECTION'] = @mysqli_connect (DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) or die('Could not connect to mysql: '. mysqli_connect_error());
    $m->setDatabaseArray($database);
    
?>
