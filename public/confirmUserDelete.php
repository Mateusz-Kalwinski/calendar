<?php

declare(strict_types=1);

$status = session_status();
if ($status == PHP_SESSION_NONE){
    session_start();
}

if (isset($_POST['user_id']) && isset($_SESSION['user'])){
    $id = (int) $_POST['user_id'];
}else{
    header("Location: ./");
    exit();
}

include_once '../sys/core/init.int.php';

$user =new Users($dbo);
$deleteUser = $user->confirmUserDelete($id);

$page_title = 'Usuwanie użytkownika';
$css_files = array('style.css');
$css_files[] = 'materialize.css';

include_once 'assets/common/header.inc.php';

?>

    <div class="container">
        <?=$deleteUser?>
    </div>

<?php
include_once 'assets/common/footer.inc.php';
?>