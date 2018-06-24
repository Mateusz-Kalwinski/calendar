<?php

declare(strict_types=1);

$status = session_status();
if ($status == PHP_SESSION_NONE){
    session_start();
}

if (isset($_POST['event_id']) && isset($_SESSION['user'])){
    $id = (int) $_POST['event_id'];
}else{
    header("Location: ./");
    exit();
}

include_once '../sys/core/init.int.php';

$cal =new Calendar($dbo);
$markup = $cal->confirmDelete($id);

$page_title = 'Zobacz wydarzenie';
$css_files = array('style.css');
$css_files[] = 'materialize.css';

include_once 'assets/common/header.inc.php';

?>

<div class="container">
    <?=$markup?>
</div>

<?php
include_once 'assets/common/footer.inc.php';
?>
