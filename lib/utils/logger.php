<?php
//logging consts
define('PRINTALL', -1);
define('ALL', 0);
define('DEBUG', 1);
define('NOTICE', 2);
define('WARNING', 3);
define('ERROR', 4);
define('ALERT', 5);
define('NONE', 10);

class logger
{
  public static function log($msg, $severity=ERROR)
  {
    if($severity >= config::loggerSeverity && config::loggerSeverity != NONE)
    {
      if(config::backtrace)
      {
        $backtrace = debug_backtrace();
        $lastCall = $backtrace[0];
        $msg = $msg.' - '.$lastCall['file'].':'.$lastCall['line'];
      }
      error_log($msg);
    }

    //if logging is -1 then print
    if (config::loggerSeverity == PRINTALL)
    {
      echo $msg."\n\n";
    }
  }
}
?>