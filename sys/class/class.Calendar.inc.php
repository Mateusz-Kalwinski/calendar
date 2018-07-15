<?php
declare(strict_types=1);
ini_set('display_errors', '1');

include_once 'class.DB_Connect.inc.php';
include_once 'class.Mail.inc.php';

class Calendar extends DB_Connect{
    private $_useDate;
    private $_m;
    private $_y;
    private $_daysInMonth;
    private $_startDay;

    public function __construct($dbo = NULL, $useDate = NULL)
    {
        parent::__construct($dbo);
        if (isset($useDate)){
            $this->_useDate = $useDate;
        }else{
            $this->_useDate =date('Y-m-d H:i:s');
        }

        $ts = strtotime($this->_useDate);
        $this->_m =(int)date('m', $ts);
        $this->_y = (int)date('Y', $ts);

        $this->_daysInMonth = cal_days_in_month(
            CAL_GREGORIAN,
            $this->_m,
            $this->_y
        );

        $ts =mktime(0,0,0,$this->_m, 1, $this->_y);
        $this->_startDay =  (int)date('w', $ts);
    }

    private function _loadEventData($id = NULL){

        $sql = "SELECT
                `event_id`, `event_title`, `event_desc`, `event_start`, `event_end`, `event_from`, `event_to`, `NFZ`
                FROM `events`";

        if (!empty($id)){
            $sql .= "WHERE `event_id` = :id LIMIT 1";
        }else{
            $start_ts = mktime(0,0,0, $this->_m, 1, $this->_y);
            $end_ts = mktime(23, 59,59, $this->_m+1, 0, $this->_y);
            $start_date =date('Y-m-d H:i:s', $start_ts);
            $end_date = date('Y-m-d H:i:s', $end_ts);

            $sql .= "WHERE `event_start` BETWEEN '$start_date' AND '$end_date' ORDER BY `event_start`";
        }
        try{
            $stmt = $this->db->prepare($sql);

            if (!empty($id)){
                $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            }
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $result;
        }
        catch (Exception $e){
            die($e->getMessage());
        }
    }
    private function _createEventObj(){

        $arr = $this->_loadEventData();

        $events = array();
        foreach ($arr as $event){
            $day = date('j', strtotime($event['event_start']));

            try{
                $events[$day][] = new Event($event);
            }catch (Exception $e){
                die ($e->getMessage());
            }
        }
        return $events;
    }
    public function buildCalendar()
    {

        $cal_year = date('Y', strtotime($this->_useDate));
        $cal_month =date('m', strtotime($this->_useDate));

        define('WEEKDAYS', array('Nd', 'Pn', 'Wt', 'Śr', 'Cz', 'Pt', 'So'));
        $polish_month =array(
            '01'=>'Styczeń ',
            '02'=>'Luty ',
            '03' =>'Marzec ',
            '04' => 'Kwiecień ',
            '05' => 'Maj ',
            '06' => 'Czerwiec ',
            '07' => 'Lipiec ',
            '08' => 'Sierpnień ',
            '09' => 'Wrzesień ',
            '10' => 'Październik ',
            '11' => 'Listopad ',
            '12' => 'Grudzień '
        );

        $html = "<div class='content z-depth-5'><h2 class='center-align'>$polish_month[$cal_month] $cal_year</h2><table class='responsive-table'>";

        for ( $d=0, $labels=NULL; $d<7; ++$d )
        {
            $labels .= "<td class='green-color size'>" . WEEKDAYS[$d] . "</td>";
        }
        $html .=
             $labels . "</tr>";
        $events = $this->_createEventObj();

        $html .= "<tr>";
        for ( $i=1, $c=1, $t=date('j'), $m=date('m'), $y=date('Y');
              $c<=$this->_daysInMonth; ++$i )
        {
            $class = $i<=$this->_startDay ? "fill" : NULL;

            if ( $c==$t && $m==$this->_m && $y==$this->_y )
            {
                $class = " today";
            }

            $ls = sprintf("<td class=\"size days%s\">", $class);
            $le = "</td>";
            $event_info = NULL;

            if ( $this->_startDay<$i && $this->_daysInMonth>=$c)
            {
                if ( isset($events[$c]) )
                {
                    foreach ( $events[$c] as $event )
                    {
                        if ($event->NFZ == '1'){
                            $isNFZ = '<div class="NFZdot"></div>';
                        }else{
                            $isNFZ ='';
                        }
                        $link = '<a class="event indigo-text text-lighten-2" href="view.php?event_id='
                            . $event->id . '">' . $event->title
                            . '</a>';
                        $event_info .="$isNFZ" . "$link";
                    }
                }

                $date = sprintf("\n\t\t\t<strong>%02d</strong><br>",$c++);
            }
            else { $date="&nbsp;"; }

            $wrap = $i!=0 && $i%7==0 ? "</tr><tr>" : NULL;

            $html .= $ls . $date . $event_info . $le . $wrap;
        }

        while ( $i%7!=1 )
        {
            $html .= "<td class=\"fill\">&nbsp;</td>";
            ++$i;
        }

        $html .= "</tr></table></div>";


        return $html;
    }

    public function displayEvent($id)
    {
        if ( empty($id) ) { return NULL; }

        $id = preg_replace('/[^0-9]/', '', $id);

        $event = $this->_loadEventById($id);

        $ts = strtotime($event->start);
        $te = strtotime($event->end);
        $date_start = date('d-m-Y', $ts);
        $start = date('G:i:s', $ts);
        $date_end = date('d-m-Y', $te);
        $end = date('G:i:s', $te);

        $admin = $this->_adminEntryOptions($id);
        if ($event->NFZ == 1 ){
            $isNFZ = 'Tak';
        }else{
            $isNFZ = 'Nie';
        }
        return <<<EVENT_VIEW
            <div class="eventV">
                <h3 class="center-align">Szczegóły wezwania</h3>
                    <div class="row">
                        <div class="col s6 m5 l2">
                            <p class="teal-text text-lighten-2 ">Nazwa wezwania:</p>
                        </div>
                        <div class="col s6 m7 l10">
                            <p class="indigo-text text-lighten-2">$event->title</p>
                        </div>
                        
                        <div class="col s6 m5 l2">
                            <p class="teal-text text-lighten-2 ">Data rozpoczęcia: </p>
                        </div>
                        <div class="col s6 m7 l10">
                            <p class="indigo-text text-lighten-2">$date_start $start</p>
                        </div>
                        
                        <div class="col s6 m5 l2">
                            <p class="teal-text text-lighten-2 ">Data zakończenia: </p>
                        </div>
                        <div class="col s6 m7 l10">
                            <p class="indigo-text text-lighten-2">$date_end $end</p>
                        </div>
                        
                        <div class="col s6 m5 l2">
                            <p class="teal-text text-lighten-2 ">Adres początkowy: </p>
                        </div>
                        <div class="col s6 m7 l10">
                            <p class="indigo-text text-lighten-2">$event->from</p>
                        </div>
                        
                        <div class="col s7 m5 l2">
                            <p class="teal-text text-lighten-2 ">Adres zakończenia: </p>
                        </div>
                        <div class="col s5 m7 l10">
                            <p class="indigo-text text-lighten-2">$event->to</p>
                        </div>
                        
                        <div class="col s6 m5 l2">
                            <p class="teal-text text-lighten-2 ">Opis: </p>
                        </div>
                        <div class="col s6 m7 l10">
                            <p class="indigo-text text-lighten-2">$event->description</p>
                        </div>
                        <div class="col s6 m5 l2">
                            <p class="teal-text text-lighten-2 ">Wezwanie NFZ:</p>
                        </div>
                        <div class="col s6 m7 l10">
                            <p class="indigo-text text-lighten-2">$isNFZ</p>
                        </div>
                    </div>
                </div>
                $admin
            </div>
EVENT_VIEW;

    }



    private function _adminEntryOptions($id)
    {
        return <<<ADMIN_OPTIONS

    <div class="admin-options center-login">
    <form action="admin.php" method="post">
        <div class="input-field">
            <button class="waves-effect waves-light btn padding-1" type="submit" name="edit_event"><i class="material-icons left">mode_edit</i>Edytuj wezwanie</button>
            <input type="hidden" name="event_id"
                  value="$id" />
        </div>
    </form>
    <form action="confirmdelete.php" method="post">
        <div class="input-field">
            <button class="waves-effect waves-light blue-grey darken-2 btn padding-1" type="submit" name="delete_event"><i class="material-icons left">delete</i>Usuń wezwanie</button>
            <input type="hidden" name="event_id" value="$id">
        </div>
    </form>
    </div>
ADMIN_OPTIONS;
    }

    public function displayForm()
    {
        if ( isset($_POST['event_id']) )
        {
            $id = (int)$_POST['event_id'];
        }
        else
        {
            $id = NULL;
        }

        $submit = "Utwórz nowe wydarzenie";

        $event = new Event();

        if ( !empty($id))
        {
            $event = $this->_loadEventById($id);
            $explode_event_start = explode(' ', $event->start);
            $explode_event_end = explode(' ', $event->end);

            if ( !is_object($event) ) { return NULL; }

            $submit = "Edytuj wydarzenie";
        }else{
            $explode_event_start[0] = '';
            $explode_event_start[1] = '';
            $explode_event_end[0] = '';
            $explode_event_end[1] = '';
        }
        return <<<FORM_MARKUP
    <form class="app-form" action="assets/inc/process.inc.php" method="post" autocomplete="off">
            <h3 class="center-align">$submit</h3>
            
            <div class="input-field">
                <i class="material-icons prefix indigo-text text-lighten-2">assignment</i>
                <input class="validate" type="text" name="event_title"minlength="1" maxlength="40" data-length="40" id="event_title" value="$event->title" autocomplete="off" />
                <label for="event_title">Nazwa wydarzenia</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix indigo-text text-lighten-2">date_range</i>
                <input class="datepicker" type="text" name="event_date_start" id="event_date_start" value="$explode_event_start[0]"/>
                <label for="event_date_start">Data rozpoczęcia</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix indigo-text text-lighten-2">access_time</i>
                <input class="timepicker" type="text" name="event_time_start" id="event_time_start" value="$explode_event_start[1]"/>
                <label for="event_time_start">Czas rozpoczęcia</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix indigo-text text-lighten-2">date_range</i>
                <input class="datepicker_end" type="text" name="event_date_end" id="event_date_end" value="$explode_event_end[0]"/>
                <label for="event_date_end">Data zakończenia</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix indigo-text text-lighten-2">access_time</i>
                <input class="timepicker" type="text" name="event_time_end" id="event_time_end" value="$explode_event_end[0]"/>
                <label for="event_time_end">Czas zakończenia</label>
            </div>
            
            <div class="input-field">
                <i class="material-icons prefix indigo-text text-lighten-2">person_pin_circle</i>
                <input class="validate" type="text" name="event_from"
                      id="event_from" value="$event->from" autocomplete="off"/>
                <label for="event_from">Adres początkowy</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix indigo-text text-lighten-2">near_me</i>
                <input class="validate" type="text" name="event_to"
                  id="event_to" value="$event->to" autocomplete="off"/>
                <label for="event_to">Adres końcowy</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix indigo-text text-lighten-2">create</i>
                <textarea class="materialize-textarea" name="event_description"
                      id="event_description">$event->description</textarea>
                <label for="event_description">Opis wydarzenia</label>
            </div>
            <p>
                <label>
                    <input type="checkbox" name="NFZ" value="1"/>
                    <span>Wezwanie NFZ</span>
              </label>
            </p>
            <input type="hidden" name="event_id" value="$event->id" />
            <input type="hidden" name="token" value="$_SESSION[token]" />
            <input type="hidden" name="action" value="event_edit" />
            <div class="center-login">
                <div class="col s7">
                    <button class="waves-effect waves-light btn padding-1" name="event_submit" value="$submit"><i class="material-icons left">send</i>$submit</button>
                    <button class="waves-effect waves-light btn red accent-2 padding-1"><i class="material-icons left">cancel</i><a class="white-text" href="./">Anuluj</a></button>
                </div>
            </div>
    </form>
FORM_MARKUP;
    }
//kod
    public function processForm(){
        if ($_POST['action'] != 'event_edit'){
            return 'Ups, coś poszło nie tak!';
        }

        $title = htmlentities($_POST['event_title'], ENT_QUOTES);
        $desc = htmlentities($_POST['event_description'], ENT_QUOTES);
        $start_date = htmlentities($_POST['event_date_start'], ENT_QUOTES);
        $start_time = htmlentities($_POST['event_time_start'], ENT_QUOTES);
        $end_date = htmlentities($_POST['event_date_end'], ENT_QUOTES);
        $end_time = htmlentities($_POST['event_time_end'], ENT_QUOTES);
        $from = htmlentities($_POST['event_from'], ENT_QUOTES);
        $to = htmlentities($_POST['event_to'], ENT_QUOTES);

        if (isset($_POST['NFZ']) && $_POST['NFZ'] == '1'){
            $isNFZ_1 = '1';
            $NFZ = htmlentities($isNFZ_1, ENT_QUOTES);
        }else{
            $isNFZ_0 = '0';
            $NFZ = htmlentities($isNFZ_0, ENT_QUOTES);
        }
        $start_event = $start_date. ' ' . $start_time.':00';
        $end_event = $end_date. ' ' . $end_time.':00';
        var_dump($start_event);

        $start =htmlentities($start_event, ENT_QUOTES);
        $end = htmlentities($end_event, ENT_QUOTES);
        if (empty($_POST['event_id'])){
            $sql = "INSERT INTO `events`
                    (`event_title`, `event_desc`, `event_start`, `event_end`, `event_from`, `event_to`, `NFZ`)
                    VALUES (:title, :description, :start, :end, :from, :to, :NFZ)";
        }else{
            $id = (int) $_POST['event_id'];
            $sql = "UPDATE `events`
                    SET
                     `event_title`=:title,
                      `event_desc`=:description,
                      `event_start`=:start,
                      `event_end`=:end,
                      `event_from`=:from,
                      `event_to`=:to,
                      `NFZ`=:NFZ
                  WHERE `event_id` = $id";
        }

        try{
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":title", $title, PDO::PARAM_STR);
            $stmt->bindParam(":description", $desc, PDO::PARAM_STR);
            $stmt->bindParam(":start", $start, PDO::PARAM_STR);
            $stmt->bindParam(":end", $end, PDO::PARAM_STR);
            $stmt->bindParam(":from", $from, PDO::PARAM_STR);
            $stmt->bindParam(":to", $to, PDO::PARAM_STR);
            $stmt->bindParam(":NFZ", $NFZ, PDO::PARAM_STR);
            $stmt->execute();
            $stmt->closeCursor();

            $user_obj = new Users();
            $users_email = $user_obj->getUsers();
            
            if (empty($_POST['event_id'])){

                foreach ($users_email as $value){
                    $recipment = $value['user_email'];
                    $subject = 'Dodano wezwanie: ' . $title;
                    $body = '<p>Data rozpoczęcia: '.$start.'</p>
                            <p>Data zakończnia: '.$end.'</p>
                            <p>Adres początkowy: '.$from.'</p>
                            <p>Adres zakończenia: '.$to.'</p>
                            <p>Opis: '.$desc.'</p>
                            <p>Wezwanie NFZ: '.$NFZ.'</p>';
                    $mail = new Mail();
                    $mail->valueMail($recipment, $subject, $body);
                }
            }else{
                foreach ($users_email as $value){
                    $recipment = $value['user_email'];
                    $subject = 'Edytowano wezwanie: ' . $title;
                    $body = '<p>Data rozpoczęcia: '.$start.'</p>
                            <p>Data zakończnia: '.$end.'</p>
                            <p>Adres początkowy: '.$from.'</p>
                            <p>Adres zakończenia: '.$to.'</p>
                            <p>Opis: '.$desc.'</p>
                            <p>Wezwanie NFZ: '.$NFZ.'</p>';
                    $mail = new Mail();
                    $mail->valueMail($recipment, $subject, $body);
                }
            }
            return TRUE;
        }catch (Exception $e){
            return $e->getMessage();
        }
    }

    public function confirmDelete($id){
        if (empty($id)){
            return NULL;
        }
        $id = preg_replace('/[^0-9]/', '', $id);
        $event = $this->_loadEventById($id);
        if (!is_object($event)){
            header("Location: ./");
        }
        if (isset($_POST['confirm_delete']) && $_POST['token'] == $_SESSION['token']){
            if ($_POST['confirm_delete'] == 'Tak, usuń to wydarzenie'){
                $sql = "DELETE FROM `events` WHERE `event_id` = :id LIMIT 1";
                try{
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt->closeCursor();

                    $user_obj = new Users();
                    $users_email = $user_obj->getUsers();


                    foreach ($users_email as $value){
                        $recipment = $value['user_email'];
                        $subject = 'Usunięto wezwanie: ' . $event->title;
                        $body = 'wezwanie zostało usunięte';
                        $mail = new Mail();
                        $mail->valueMail($recipment, $subject, $body);
                    }
                    header("Location: ./");
                    return;
                }catch (Exception $e){
                    return $e->getMessage();
                }
            }else{
                header("Location: ./");
                return;
            }
        }

        return <<<CONFIRM_DELETE
        <div class="content">
        <form class="app-form" action="confirmdelete.php" method="post">
            <h4 class="center-align">Czy napewno chcesz usunąć: <span class="teal-text text-lighten-2">$event->title</span>?</h4>
            <br>
            <p class="center-align indigo-text text-lighten-2 light-font">Tej operacji nie można cofnąć!</p>
            
            <div class="center-login">
                <div class="input-field">
                    <button class="waves-effect waves-light btn blue-grey darken-2 btn padding-1" type="submit" name="confirm_delete" value="Tak, usuń to wydarzenie"><i class="material-icons left">delete</i>Tak, usuń to wydarzenie</button>
                </div>
                <div class="input-field">
                    <a class="white-text waves-effect waves-light btn red accent-2 padding-1" href="./"><i class="material-icons left">cancel</i>Anuluj</a>
                 </div>
                <input type="hidden" name="event_id" value="$event->id">
                <input type="hidden" name="token" value="$_SESSION[token]">
            </div>
        </form>
</div>
CONFIRM_DELETE;
    }

    //tutaj

    public function displayFormAddUser(){
        $submit = 'Dodaj użytkownika';
        return <<<FORM_ADDUSER
        <form class="app-form" action="assets/inc/process.inc.php" method="post" autocomplete="off">
            <h3 class="center-align">$submit</h3>
            <div class="input-field">
                <i class="material-icons prefix indigo-text text-lighten-2">account_box</i>
                <input class="validate" type="text" name="user_name" id="user_name" value="" autocomplete="off">
                <label for="user_name">Nazwa użytkownika</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix indigo-text text-lighten-2">email</i>
                <input class="validate" type="text" name="user_email" id="user_email" autocomplete="off">
                <label for="user_email">E-mail użytkownika</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix indigo-text text-lighten-2">fingerprint</i>
                <input class="validate" type="password" name="user_pass" id="user_pass" autocomplete="off">
                <label for="user_pass">Hasło</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix indigo-text text-lighten-2">fingerprint</i>
                <input class="validate" type="password" name="user_pass_confirm" id="user_pass_confirm" autocomplete="off">
                <label for="user_pass_confirm">Potwierdź hasło</label>
            </div>
            <input type="hidden" name="token" value="$_SESSION[token]" />
            <input type="hidden" name="action" value="add_user" />
            <div class="center-login">
            <div class="col s7">
                <button class="waves-effect waves-light btn padding-1" name="event_submit" value="$submit"><i class="material-icons left">send</i>$submit</button>
                <button class="waves-effect waves-light btn red accent-2 padding-1"><i class="material-icons left">cancel</i><a class="white-text" href="./">Anuluj</a></button>
            </div>
        </div>
        </form>
FORM_ADDUSER;
    }

    public function processFormAddUser()
    {
        if ($_POST['action'] != 'add_user') {
            return 'Ups, coś poszło nie tak!';
        }

        $userName = htmlentities($_POST['user_name'], ENT_QUOTES);
        $userEmail = htmlentities($_POST['user_email'], ENT_QUOTES);
        $userPass = htmlentities($_POST['user_pass'], ENT_QUOTES);
        $userPassConfirm = htmlentities($_POST['user_pass_confirm'], ENT_QUOTES);

        if ($userPass == $userPassConfirm) {

            $adminPass = new Admin();
        $userPass = $adminPass->SaltedHash($userPass);


            $sql = "INSERT INTO `users`
                    (`user_name`, `user_pass`, `user_email`)
                    VALUES (:userName, :userPass, :userEmail)";
            try {
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(":userName", $userName, PDO::PARAM_STR);
                $stmt->bindParam(":userPass", $userPass, PDO::PARAM_STR);
                $stmt->bindParam(":userEmail", $userEmail, PDO::PARAM_STR);
                $stmt->execute();
                $stmt->closeCursor();

                return TRUE;


            } catch (Exception $e) {
                return $e->getMessage();
            }
        }else{
            return 'Niezgodne hasło!';
        }

    }



    private function _loadEventById($id){
        if (empty($id)){
            return NULL;
        }

        $event = $this->_loadEventData($id);

        if (isset($event[0])){
            return new Event($event[0]);
        }else{
            return NULL;
        }
    }


}
