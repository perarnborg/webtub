<?php
header("Content-type: text/html; charset=utf-8");
require_once 'HTTP/OAuth/Consumer.php';
$consumer = new HTTP_OAuth_Consumer(constant('PUBLIC_KEY'), constant('PRIVATE_KEY'), $_SESSION['accessToken'], $_SESSION['accessTokenSecret']);
?>
hejs