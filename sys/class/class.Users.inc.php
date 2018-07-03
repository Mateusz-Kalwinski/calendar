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
            echo '<div class="container">
<table class="responsive-table centered highlight"
                    <thead>
                        <tr>
                            <th>Nazwa użytkownika</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($users as $user){
            echo "<tr>
                    <td>$user[user_name]</td>
                    <td>$user[user_email]</td>
                  </tr>";
        }
        echo '</tbody></table></div>';
    }

    private function getUsers(){
        $sql = "SELECT `user_id`, `user_name`, `user_email`
                FROM `users`";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $users;
    }
}
