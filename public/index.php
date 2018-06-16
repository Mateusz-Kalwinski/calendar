<?php

declare(strict_types=1);

include_once '../sys/core/init.int.php';

$cal = new Calendar($dbo, "20   18-06-01 12:00:00");

echo $cal->buildCalendar();