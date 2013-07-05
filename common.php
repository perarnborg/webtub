<?php

session_start();

require_once 'HTTP/OAuth/Consumer.php';

define('PUBLIC_KEY', 'FEHUVEW84RAFR5SP22RABURUPHAFRUNU');
define('PRIVATE_KEY', 'ZUXEVEGA9USTAZEWRETHAQUBUR69U6EF');

define('URL', 'http://api.telldus.com');
define('REQUEST_TOKEN', constant('URL').'/oauth/requestToken');
define('AUTHORIZE_TOKEN', constant('URL').'/oauth/authorize');
define('ACCESS_TOKEN', constant('URL').'/oauth/accessToken');
define('REQUEST_URI', constant('URL').'/json');

define('BASE_URL', 'http://'.$_SERVER["SERVER_NAME"].':8888');

define('TELLSTICK_TURNON', 1);
define('TELLSTICK_TURNOFF', 2);