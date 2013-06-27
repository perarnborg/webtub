<?php 
header("Content-type: text/html; charset=utf-8");
require_once 'HTTP/OAuth/Consumer.php';
require_once 'config/telldus.php';
//get url parameter and include right file.
$parameter = isset($_GET['p'])?$_GET['p']:'';
//session_destroy();
$parameters = explode('/',$parameter);


/* AJAX callback pages */
switch($parameters[0])
{ 
  case 'test':
    break;
}
?>