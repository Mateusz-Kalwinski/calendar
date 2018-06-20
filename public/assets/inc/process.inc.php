<?php

declare(strict_types=1);

$status = session_status();
if ($status == PHP_SESSION_NONE){
    session_start();
}
include_once '../../../sys/config/db-cred.inc.php';

foreach ($C as $name=>$val){
    define($name, $val);
}

define('ACTIONS', array(
    'event_edit' =>array(
        'object' => 'Calendar',
        'method' =>'processForm',
        'header' => 'Location: ../../'
        )
    )
);

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
$dbo = new PDO($dsn, DB_USER, DB_PASS);
$dbo->query("SET NAMES 'utf8'");
