<?php
define('TELLDUS_PUBLIC_KEY', 'FEHUVEW84RAFR5SP22RABURUPHAFRUNU');
define('TELLDUS_PRIVATE_KEY', 'ZUXEVEGA9USTAZEWRETHAQUBUR69U6EF');

define('TELLDUS_URL', 'http://api.telldus.net');
define('TELLDUS_REQUEST_TOKEN', constant('TELLDUS_URL').'/oauth/requestToken');
define('TELLDUS_AUTHORIZE_TOKEN', constant('TELLDUS_URL').'/oauth/authorize');
define('TELLDUS_ACCESS_TOKEN', constant('TELLDUS_URL').'/oauth/accessToken');
define('TELLDUS_REQUEST_URI', constant('TELLDUS_URL').'/json');

define('TELLDUS_BASE_URL', 'http://'.$_SERVER["SERVER_NAME"].':8888');

define('TELLDUS_TELLSTICK_TURNON', 1);
define('TELLDUS_TELLSTICK_TURNOFF', 2);
?>