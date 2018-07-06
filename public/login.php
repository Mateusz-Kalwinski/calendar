<?php

declare(strict_types=1);

include_once '../sys/core/init.int.php';

$page_title = 'Zaloguj się';
$css_files = array('style.css');
$css_files[] = 'materialize.css';
include_once 'assets/common/header.inc.php';

?>

<div class="container">
    <form class="forms" action="assets/inc/process.inc.php" method="post" autocomplete="off">
            <div class="input-field">
                <i class="material-icons prefix">account_circle</i>
                <input id="uname" type="text" class="validate" name="uname" value="" autocomplete="off">
                <label for="uname">Nazwa użytkownika</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">mode_edit</i>
                <input id="pword" type="password" class="validate" name="pword" value="" autocomplete="off">
                <label for="pword">Hasło</label>
            </div>
            <input type="hidden" name="token" value="<?=$_SESSION['token']?>">
            <input type="hidden" name="action" value="user_login">
            <div class="center-login">
                <div class="col s8">
                    <button class="waves-effect waves-light btn padding-1" type="submit" name="login_submit"><i class="material-icons left">send</i>Zaloguj się</button>
                    <button class="waves-effect waves-light btn red accent-2 padding-1"><i class="material-icons left">cancel</i><a class="white-text" href="./">Anuluj</a></button>
                </div>
            </div>
        </div>
    </form>
<?php
include_once 'assets/common/footer.inc.php';
?>
