<?php

declare(strict_types=1);

include_once '../sys/core/init.int.php';

$page_title = "Kalendarz wydarzeń";
$css_files = array('style.css');
$css_files[] = 'materialize.css';

include_once 'assets/common/header.inc.php';

if (isset($_SESSION['user'])) {

    $test = new Users();
    $test->buildUsers();
}
else{
    header("Location: login.php");
}

include_once 'assets/common/footer.inc.php';
