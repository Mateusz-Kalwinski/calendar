<?php
declare(strict_types=1);
ini_set('display_errors', '1');

include_once 'class.DB_Connect.inc.php';

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
                `event_id`, `event_title`, `event_desc`, `event_start`, `event_end`
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
            var_dump($result);
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
    public function buildCalendar(){

        $cal_month = date('F Y', strtotime($this->_useDate));

        define('WEEKDAYS', array('Nd', 'Pon', 'Wt', 'Śr', 'Czw', 'Pt', 'So'));

        $html = "\n\t<h2>$cal_month</h2>";
        for ( $d=0, $labels=NULL; $d<7; ++$d ) {
            $labels .= "\n\t\t<li>" . WEEKDAYS[$d] . "</li>";
        }
        $html .="\n\t<ul class='weekdays'>$labels\n\t</ul>";

        return $html;
    }
}
