<?php

declare(strict_types=1);

include_once '../sys/core/init.int.php';

if (!isset($_SESSION['user'])){
    header('Location: login.php');
    exit();
}

$page_title = 'Dodaj/Edytuj wydarzenie';
$css_files =  array('style.css');
$css_files[] = 'materialize.css';

include_once 'assets/common/header.inc.php';

$cal = new Calendar($dbo);

?>

<div class="container">
    <?=$cal->displayForm()?>
</div>
<?php
include_once 'assets/common/footer.inc.php'
?>