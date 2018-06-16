<?php

declare(strict_types=1);

include_once '../sys/core/init.int.php';

$cal = new Calendar($dbo, "20   18-06-01 12:00:00");

if (is_object($cal)){
    echo "<pre>", var_dump($cal), "</pre>";
}