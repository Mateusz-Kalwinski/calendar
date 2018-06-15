<?php

declare(strict_types=1);

class DB_Connect {
    protected $db;

    protected function __construct($db=NULL)
    {
        if (is_object($db)){
            $this->db =$db;
        }else{
            $dns = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
            try{
                $this->db = new PDO($dns, DB_USER, DB_PASS);
//                $dbo ='';
                $dbo->query("SET NAMES 'utf8'");
            }catch (Exception $e){
                die($e->getMessage());
            }
        }
    }
}
