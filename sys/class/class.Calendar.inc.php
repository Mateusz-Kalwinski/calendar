<?php

declare(strict_types=1);

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

            $sql .= "WHERE `event_start` BEETWEN '$start_date' AND '$end_date' ORDER BY `event_start`";
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
}
