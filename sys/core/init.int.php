<?php

declare(strict_types=1);

include_once '../sys/config/db-cred.inc.php';

foreach ($C as $name => $val){
    define($name, $val);
}

$dns = "mysql:host=" . DB_HOST . ";dbname" . DB_NAME;
$dbo = new PDO($dns, DB_USER, DB_PASS);
$dbo->query("SET NAME 'utf8'");

function __autoload($class) {
    $filename = "../sys/class/class.". $class .".inc.php";
    if (file_exists($filename)){
        include_once ($filename);
    }
}
