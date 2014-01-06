<?php
header("Content-type: text/html; charset=utf-8");
require_once 'HTTP/OAuth/Consumer.php';
require_once 'config/main.php';
require_once config::libDir().'/webtub/cron.php';

//get url parameter and include right file.
$parameter = isset($_GET['p'])?$_GET['p']:'';

$cron = new cron();
$cron->checkTubs();

?>
