<?php
header("Content-type: text/html; charset=utf-8");
require_once 'config/main.php';
$timer = new timer();
//get url parameter and include right file.
$parameter = isset($_GET['p'])?$_GET['p']:'';
logger::log('Start of request: '.$parameter, ALL);


logger::log('Time to render page: '. $timer->endTimer() .' Request: '. $parameter, ALL);
?>