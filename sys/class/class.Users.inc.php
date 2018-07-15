<?php

declare(strict_types=1);

include_once 'class.DB_Connect.inc.php';

class Users extends DB_Connect{


    public function __construct($db = NULL)
    {
        parent::__construct($db);

    }

    public function buildUsers(){
        $users = $this->getUsers();
            echo '<div class=\'content z-depth-5 userPadding\'><h3 class=\'center-align\'>Panel użytkowników</h3>
<table class="responsive-table centered highlight light-font">
                    <thead>
                        <tr>
                            <th class="center-align green-color light-font">Nazwa użytkownika</th>
                            <th class="center-align green-color light-font">Email</th>
                            <th class="center-align green-color light-font">Usuń</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($users as $user){
            echo "<tr>
                    <td class='indigo-text text-lighten-2'>$user[user_name]</td>
                    <td class='indigo-text text-lighten-2'>$user[user_email]</td>
                    <td>
                        <form action='confirmUserDelete.php' method='post'>
                            <div class='input-field'>
                                <button class=\"waves-effect waves-light btn blue-grey darken-2 btn padding-1\" type='submit' name='delete_user' value='Usuń'><i class=\"material-icons left\">delete</i>Usuń</button>
                                <input type='hidden' name='user_name' value='$user[user_name]'>
                                <input type='hidden' name='user_id' value='$user[user_id]'>  
                             </div>             
                        </form>
                    </td>
                  </tr>";
        }
        echo '</tbody></table>';
    }

    public function getUsers(){
        $sql = "SELECT `user_id`, `user_name`, `user_email`
                FROM `users`";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $users;
    }
    public function confirmUserDelete($id){
        if (empty($id)){
            return NULL;
        }

        $id = preg_replace('/[^0-9]/', '', $id);
            if (isset($_POST['confirm_user_delete']) && $_POST['token'] == $_SESSION['token']){
                if ($_POST['confirm_user_delete'] == 'Tak, usuń tego użytkownika'){
                    $sql = 'DELETE FROM `users` WHERE `user_id` = :id LIMIT 1';
                    try{
                        $stmt = $this->db->prepare($sql);
                        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                        $stmt->execute();
                        $stmt->closeCursor();

                        header("Location: users.php");
                        return;
                    }catch (Exception $e){
                        return $e->getMessage();
                    }
                }else{
                    header("Location: ./");
                    return;
                }
            }

            return <<<CONFIRM_USER_DELETE
            <div class="content">
                <form class="app-form" action="confirmUserDelete.php" method="post">
                    <h4 class="center-align">Czy napewno chcesz usunąć użytkownika <span class="teal-text text-lighten-2">"$_POST[user_name]"</span>?</h4>
                    <br>
                    <p class="center-align indigo-text text-lighten-2 light-font">tej operacji nie można cofnąć!</p>
                    <div class="center-login">
                        <div class="input-field">
                            <button class="waves-effect waves-light btn blue-grey darken-2 btn padding-1" type="submit" name="confirm_user_delete" value="Tak, usuń tego użytkownika">Tak, usuń tego użytkownika</button>
                        </div>
                        <div class="input-field">
                            <a class="white-text waves-effect waves-light btn red accent-2 padding-1" href="./"><i class="material-icons left">cancel</i>Anuluj</a>
                        </div>
                    </div>
                    <input type="hidden" name="user_id" value="$_POST[user_id]">
                    <input type="hidden" name="token" value="$_SESSION[token]">
                </form>
            </div>
CONFIRM_USER_DELETE;

    }
}
