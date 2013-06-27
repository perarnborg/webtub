<?php
/**
* config class
* Main application config
*
*/

//if cli use other root path mostly for unittests
$root = (PHP_SAPI == 'cli' && empty($_SERVER['REMOTE_ADDR'])) ? '.' : $_SERVER['DOCUMENT_ROOT'];

//Autoload functionality
// $incPaths = array('/webtub','/utils', '/utils/cacheImplements');
// set_include_path(config::libDir().implode(PATH_SEPARATOR.config::libDir(), $incPaths));

// spl_autoload_extensions('.php');
// spl_autoload_register();

// includes
require_once $root.'/config/db.php';
require_once $root.'/config/webtub.php';

require_once config::libDir().'/utils/timer.php';
require_once config::libDir().'/utils/logger.php';
require_once config::libDir().'/utils/includer.php';
require_once config::libDir().'/utils/dbmgr.php';
require_once config::libDir().'/utils/cookiemgr.php';
require_once config::libDir().'/utils/lightopenid.php';
require_once config::libDir().'/webtub/account.php';
require_once config::libDir().'/webtub/telldusdata.php';

session_start();
cookieMgr::getInstance();

class config
{  
  //---------SESSION---------//
  const sessionName = 'webtub';
  const COOKIE_NAME = 'webtub';
  const userAgent = 'webtub';

  //---------LOGGER---------//
  
  //PRINTALL, ALL, DEBUG, NOTICE, WARNING, ERROR, ALERT, NONE
  
  const loggerSeverity = DEBUG;
  const backtrace = true;
  
  //---------CACHE--------//
  
  const timeToLive = 10800;
  public static function memcacheServers() 
  {
    return (array(array('host'=>'127.0.0.1', 'port'=>'11211', 'weight'=>0)));
  }
  
  //could be one of the following: file, no, db, mem
  const activeCache = 'file';
  
  //get the curently active cache
  public static function getCache()
  {
    $cacheType = config::activeCache.'Cache';
    return new $cacheType();
  }
  
  
  //---------DIRS---------//
  
  public static function getRoot()
  {
    $root = (PHP_SAPI == 'cli' && empty($_SERVER['REMOTE_ADDR'])) ? '.' : $_SERVER['DOCUMENT_ROOT'];
    return $root;
  }
  
  public static function templateDir()
  {
    return config::getRoot() . '/tpl';
  }
  
  public static function libDir()
  {
    return config::getRoot() . '/lib';
  }
  
  public static function cacheDir()
  {
    return config::getRoot() . '/cache';
  }
  
  public static function imageDir()
  {
    return config::getRoot() . '/res/img';
  }
  
  
  //---------DB---------//
  const dbHost = db::dbHost;
  const dbName = db::dbName;
  const dbUsername = db::dbUsername;
  const dbPassword = db::dbPassword;
  
  //---------Defaults---------//
}

?>