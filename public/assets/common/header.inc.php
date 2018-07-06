<!doctype html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?=$page_title?></title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">


    <?php

        foreach ($css_files as $css){
            echo '<link rel="stylesheet" type="text/css" href=assets/css/'.$css.'>';
        }
    ?>
</head>
<body>

<?php
if (isset($_SESSION['user'])) {
    echo '
    <nav>
    <div class="nav-wrapper">
        <a href="#" class="brand-logo"><img src="assets/common/logo.png" class="logo-img"></a>
        <a href="#" data-target="mobile" class="sidenav-trigger"><i class="material-icons">menu</i></a>
        <ul class="right hide-on-med-and-down">
            <li>
                <a href="./" class = "admin"> Strona główna</a>
            </li>
            <li>
                <a href="admin.php" class = "admin"> Dodaj wydarzenie</a>
            </li>
            <li>
                <a href="addUser.php" class = "admin"> Dodaj użytkownika</a>
            </li>
            <li>
                <a href="users.php" class = "admin"> Zarządzaj użytkownikami</a>
            </li>
            <li>
                <form action="assets/inc/process.inc.php" method="post">
                        <button class="btn waves-effect waves-light" type="submit" value="Wyloguj">Wyloguj</button>
                        <input type="hidden" name="token" value="' . $_SESSION["token"] . '"

                </form>
            </li>
        </ul>
    </div>
</nav>

<ul class="sidenav" id="mobile">
    <li>
        <a href="./" class = "admin">Strona główna</a>
    </li>
    <li>
        <a href="admin.php" class = "admin"> Dodaj wydarzenie</a>
    </li>
    <li>
        <a href="addUser.php" class = "admin"> Dodaj użytkownika</a>
    </li>
    <li>
        <a href="users.php" class = "admin"> Zarządzaj użytkownikami</a>
    </li>
    <li><form action="assets/inc/process.inc.php" method="post">
            <div>
                <button class="btn waves-effect waves-light align-logout" type="submit" value="Wyloguj">Wyloguj</button>
                <input type="hidden" name="token" value="' . $_SESSION["token"] . '">
                <input type="hidden" name="action" value=user_logout>
            </div>
        </form></li>
</ul>';
}
