<?php

declare(strict_types=1);

include_once '../sys/core/init.int.php';

$cal = new Calendar($dbo, "20   18-06-01 12:00:00");

$page_title = "Kalendarz wydarzeń";
$css_files = array('style.css');
$css_files[] = 'materialize.css';

include_once 'assets/common/header.inc.php';
echo $cal->buildCalendar();
include_once 'assets/common/footer.inc.php';