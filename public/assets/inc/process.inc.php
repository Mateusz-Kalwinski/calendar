<?php

declare(strict_types=1);

session_start();

include_once '../../../sys/config/db-cred.inc.php';

foreach ( $C as $name => $val )
{
    define($name, $val);
}

define('ACTIONS', array(
        'event_edit' => array(
            'object' => 'Calendar',
            'method' => 'processForm',
            'header' => 'Location: ../../'
        ),
        'user_login' => array(
            'object' => 'Admin',
            'method' => 'processLoginForm',
            'header' => 'Location: ../../'
        ),
        'user_logout' => array(
            'object' => 'Admin',
            'method' => 'processLogout',
            'header' => 'Location: ../../login.php'
        ),
        'add_user' => array(
            'object' => 'Calendar',
            'method' => 'processFormAddUser',
            'header' => 'Location: ../../'
        )
    )
);

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
$dbo = new PDO($dsn, DB_USER, DB_PASS);
$dbo->query("SET NAMES 'utf8'");

if ( $_POST['token']==$_SESSION['token']
    && isset(ACTIONS[$_POST['action']]) )
{
    $use_array = ACTIONS[$_POST['action']];
    $obj = new $use_array['object']($dbo);
    $method = $use_array['method'];
    if ( TRUE === $msg=$obj->$method() )
    {
        header($use_array['header']);
        exit;
    }
    else
    {
        die ( $msg );
    }
}
else
{
    header("Location: ../../");
    exit;
}

function __autoload($class_name)
{
    $filename = '../../../sys/class/class.'
        .($class_name) . '.inc.php';
    if ( file_exists($filename) )
    {
        include_once $filename;
    }
}

?>