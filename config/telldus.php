<?php
define('TELLDUS_PUBLIC_KEY', '');
define('TELLDUS_PRIVATE_KEY', '');

define('TELLDUS_URL', 'http://api.telldus.net');
define('TELLDUS_REQUEST_TOKEN', constant('TELLDUS_URL').'/oauth/requestToken');
define('TELLDUS_AUTHORIZE_TOKEN', constant('TELLDUS_URL').'/oauth/authorize');
define('TELLDUS_ACCESS_TOKEN', constant('TELLDUS_URL').'/oauth/accessToken');
define('TELLDUS_REQUEST_URI', constant('TELLDUS_URL').'/json');

define('TELLDUS_BASE_URL', 'http://'.$_SERVER["SERVER_NAME"].':'.$_SERVER["SERVER_PORT"]);

define('TELLDUS_TELLSTICK_TURNON', 1);
define('TELLDUS_TELLSTICK_TURNOFF', 2);
?>