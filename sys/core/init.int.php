<?php

declare(strict_types=1);

$status = session_status();
if ($status == PHP_SESSION_NONE){
    session_start();
}

if(!isset($_SESSION['token'])){
    $_SESSION['token'] = sha1(uniqid((string)mt_rand(), TRUE));
}

include_once '../sys/config/db-cred.inc.php';

foreach ($C as $name => $val){
    define($name, $val);
}

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
$dbo = new PDO($dsn, DB_USER, DB_PASS);
$dbo->query("SET NAMES 'utf8'");

function __autoload($class) {
    $filename = "../sys/class/class.". $class .".inc.php";
    if (file_exists($filename)){
        include_once ($filename);
    }
}
