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

        if (isset($_useDate)){
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
                `event_id`, `event_title`, `event_desc`, `event_start`, `event_end`, `event_from`, `event_to`
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

        $html = "<h2>$polish_month[$cal_month] $cal_year</h2><table>";

        for ( $d=0, $labels=NULL; $d<7; ++$d )
        {
            $labels .= "<td class='weekdays'>" . WEEKDAYS[$d] . "</td>";
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

            $ls = sprintf("<td class=\"days%s\">", $class);
            $le = "</td>";
            $event_info = NULL;

            if ( $this->_startDay<$i && $this->_daysInMonth>=$c)
            {
                if ( isset($events[$c]) )
                {
                    foreach ( $events[$c] as $event )
                    {
                        $link = '<a class="event" href="view.php?event_id='
                            . $event->id . '">' . $event->title
                            . '</a>';
                        $event_info .= "$link";
                    }
                }

                $date = sprintf("\n\t\t\t<strong>%02d</strong>",$c++);
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

        $html .= "</tr></table>";

        $admin = $this->_adminGeneralOptions();

        return $html . $admin;
    }

    public function displayEvent($id)
    {
        if ( empty($id) ) { return NULL; }

        $id = preg_replace('/[^0-9]/', '', $id);

        $event = $this->_loadEventById($id);

        $ts = strtotime($event->start);
        $date = date('F d, Y', $ts);
        $start = date('g:ia', $ts);
        $end = date('g:ia', strtotime($event->end));

        $admin = $this->_adminEntryOptions($id);

        return "<h2>$event->title</h2>"
            . "<p class=dates>$date, $start&mdash;$end</p>"
            . "<p>$event->from</p>"
            . "<p>$event->description</p>$admin";
    }

    private function _adminGeneralOptions(){
        return <<<ADMIN_OPTIONS
        <a href="admin.php" class = "admin"> Dodaj wydarzenie</a>
        <form action="assets/inc/process.inc.php" method="post">
            <div>
                <input type="submit" value="Wyloguj" class="logout">
                <input type="hidden" name="token" value="$_SESSION[token]">
                <input type="hidden" name="action" value="user_logout">            
            </div>
        </form>
ADMIN_OPTIONS;
    }

    private function _adminEntryOptions($id)
    {
        return <<<ADMIN_OPTIONS

    <div class="admin-options">
    <form action="admin.php" method="post">
        <p>
            <input type="submit" name="edit_event"
                  value="Edytuj wydarzenie" />
            <input type="hidden" name="event_id"
                  value="$id" />
        </p>
    </form>
    <form action="confirmdelete.php" method="post">
        <p>
            <input type="submit" name="delete_event" value="Usuń wydarzenie">
            <input type="hidden" name="event_id" value="$id">
        </p>    
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

            if ( !is_object($event) ) { return NULL; }

            $submit = "Edytuj wydarzenie";
        }

        return <<<FORM_MARKUP

    <form action="assets/inc/process.inc.php" method="post">
        <fieldset>
            <legend>$submit</legend>
            <input type="text" name="event_title"
                  id="event_title" value="$event->title" />
            <label for="event_title">Nazwa wydarzenia</label>
            <input type="text" name="event_start"
                  id="event_start" value="$event->start" />
            <label for="event_start">Czas rozpoczęcia</label>
            <input type="text" name="event_end"
                  id="event_end" value="$event->end" />
            <label for="event_end">Czas zakończenia</label>
            <input type="text" name="event_from"
                  id="event_from" value="$event->from" />
            <label for="event_end">Adres początkowy</label>
            <input type="text" name="event_to"
                  id="event_to" value="$event->to" />
            <label for="event_end">Adres końcowy</label>
            <textarea name="event_description"
                  id="event_description">$event->description</textarea>
            <label for="event_description">Opis wydarzenia</label>
            <br>
            <input type="hidden" name="event_id" value="$event->id" />
            <input type="hidden" name="token" value="$_SESSION[token]" />
            <input type="hidden" name="action" value="event_edit" />
            <input type="submit" name="event_submit" value="$submit" />
            lub <a href="./">anuluj</a>
        </fieldset>
    </form>
FORM_MARKUP;
    }

    public function processForm(){
        if ($_POST['action'] != 'event_edit'){
            return 'Ups, coś poszło nie tak!';
        }

        $title = htmlentities($_POST['event_title'], ENT_QUOTES);
        $desc = htmlentities($_POST['event_description'], ENT_QUOTES);
        $start = htmlentities($_POST['event_start'], ENT_QUOTES);
        $end = htmlentities($_POST['event_end'], ENT_QUOTES);
        $from = htmlentities($_POST['event_from'], ENT_QUOTES);
        $to = htmlentities($_POST['event_to'], ENT_QUOTES);

        if (empty($_POST['event_id'])){
            $sql = "INSERT INTO `events`
                    (`event_title`, `event_desc`, `event_start`, `event_end`, `event_from`, `event_to`)
                    VALUES (:title, :description, :start, :end, :from, :to)";
        }else{
            $id = (int) $_POST['event_id'];
            $sql = "UPDATE `events`
                    SET
                     `event_title`=:title,
                      `event_desc`=:description,
                      `event_start`=:start,
                      `event_end`=:end,
                      `event_from`=:from,
                      `event_to`=:to
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
            $stmt->execute();
            $stmt->closeCursor();
            if (empty($_POST['event_id'])){
                $recipment = 'mateucz27@gmail.com';
                $subject = 'Dodano wydarzenie: ' . $title;
                $body = '<p>przykładowy tekst podczas dodawania wydarzenia</p>';
            }else{
                $recipment = 'mateucz27@gmail.com';
                $subject = 'Edytowano wydarzenie: ' . $title;
                $body = '<h1>przykłodowy tekst podczas edycji wydarzenia</h1>';
            }
            $mail = new Mail();
            $mail->valueMail($recipment, $subject, $body);

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

                    $recipment = 'mateucz27@gmail.com';
                    $subject = 'Usunięto wydarzenie: ' . $event->title;
                    $body = '<h1>przykłodowy tekst podczas usuwania wydarzenia</h1>';

                    $mail = new Mail();
                    $mail->valueMail($recipment, $subject, $body);

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
        <form action="confirmdelete.php" method="post">
            <h2>Czy napewno chcesz usunąć "$event->title"?</h2>
            <p>Tej operacji nie można cofnąć</p>
            <p>
                <input type="submit" name="confirm_delete" value="Tak, usuń to wydarzenie">
                <input type="submit" name="confirm_delete" value="Nie usuwaj">
                <input type="hidden" name="event_id" value="$event->id">
                <input type="hidden" name="token" value="$_SESSION[token]">
            </p>
        </form>
CONFIRM_DELETE;
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
