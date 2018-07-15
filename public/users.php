<?php

declare(strict_types=1);

include_once '../sys/core/init.int.php';

$page_title = "Kalendarz wydarzeń";
$css_files = array('style.css');
$css_files[] = 'materialize.css';

include_once 'assets/common/header.inc.php';

if (isset($_SESSION['user'])) {

    $users = new Users();

    echo '<div class="container">';
    $users->buildUsers();
    echo '</div>';
}
else{
    header("Location: login.php");
}

include_once 'assets/common/footer.inc.php';