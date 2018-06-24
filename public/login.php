<?php

declare(strict_types=1);

include_once '../sys/core/init.int.php';

$page_title = 'Zaloguj się';
$css_files = array('style.css');
$css_files[] = 'materialize.css';
include_once 'assets/common/header.inc.php';

?>

<div class="container">
    <form action="assets/inc/process.inc.php" method="post">
        <fieldset>
            <legend>Zaloguj się</legend>
            <label for="uname">Nazwa użytkownika</label>
            <input type="text" name="uname" id="uname" value="">
            <label for="pword">Hasło</label>
            <input type="password" name="pword" id="pword" value="">
            <input type="hidden" name="token" value="<?=$_SESSION['token']?>">
            <input type="hidden" name="action" value="user_login">
            <input type="submit" name="login_submit" value="Zaloguj się">
            <a href="./">Anuluj</a>
        </fieldset>
    </form>
</div>

<?php
include_once 'assets/common/footer.inc.php';
?>
