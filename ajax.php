<?php 
header("Content-type: text/html; charset=utf-8");
require_once 'config/main.php';
$timer = new timer();
//get url parameter and include right file.
$parameter = isset($_GET['p'])?$_GET['p']:'';
logger::log('Start of request: '.$parameter, ALL);
//session_destroy();
$parameters = explode('/',$parameter);


/* AJAX callback pages */
switch($parameters[0])
{ 
  case 'test':
    $data = array();
    echo json_encode($data);
    break;
}
?>