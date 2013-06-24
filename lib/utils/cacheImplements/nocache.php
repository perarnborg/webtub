<?php

require_once config::libDir().'/utils/aCache.php';

/**
 * Created on Jul 11, 2012
 */
 
 class noCache extends aCache
 {
   public function getCache($key)
   {
     return false;
   }
   
  public function setCache($key, $value)
  {
    return false;
  }
  
  public function deleteCache($key)
  {
    return false;
  }
 }
?>
