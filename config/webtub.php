<?php
$root = (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) ? '.' : $_SERVER['DOCUMENT_ROOT'];
require_once $root.'/config/main.php';
require_once $root.'/config/telldus.php';

class webtub
{
  
  //---------META---------//
  const pageTitle = 'WebTub';
  const pageDescription = 'Use WebTub to control a Telldus confugured hot tub.';
  
  //---------SESSION KEYS---------//
  const sessionKeyEmail = 'webtub_email';
  const sessionKeyFullname = 'webtub_fullname';
  const sessionKeyRequestToken = 'webtub_request_token';
  const sessionKeyRequestTokenSecret = 'webtub_request_token_secret';
  const sessionKeyAccessToken = 'webtub_access_token';
  const sessionKeyAccessTokenSecret = 'webtub_access_token_secret';
}