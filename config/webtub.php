<?php
$root = (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) ? '.' : $_SERVER['DOCUMENT_ROOT'];
require_once $root.'/config/main.php';
require_once $root.'/config/telldus.php';

class webtub
{
  
  //---------META---------//
  const pageTitle = 'WebTub';
  const pageDescription = 'Use WebTub to control a Telldus confugured hot tub.';
}