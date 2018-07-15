<?php

declare(strict_types=1);

if ( isset($_GET['event_id']) )
{
    $id = preg_replace('/[^0-9]/', '', $_GET['event_id']);

    if ( empty($id) )
    {
        header("Location: ./");
        exit;
    }
}
else
{
    header("Location: ./");
    exit;
}

include_once '../sys/core/init.int.php';

$page_title = "Zobacz wydarzenie";
$css_files = array("style.css");
$css_files[] = 'materialize.css';

include_once 'assets/common/header.inc.php';

$cal = new Calendar($dbo);
if (!isset($_SESSION['user'])){
    header('Location: login.php');
    exit();
}

?>

<div class="container">
    <div class="content">
        <?=$cal->displayEvent($id)?>
    </div>
</div>

<?php
include_once 'assets/common/footer.inc.php';
?>
