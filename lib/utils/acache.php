<?php
/*
 * Created on Jul 11, 2012
 * 
 * abstract class for the different cache classes
 */
 
abstract class aCache {
  abstract protected function getCache($key);
  abstract protected function setCache($key, $value);
  abstract protected function deleteCache($key);
  
  public function getCacheKey($source, $keys) 
  {
    $key = $source . '_' . (is_array($keys) ? implode('_', $keys) : $keys);
    logger::log('Getting key: '.$key, DEBUG);
    return md5($key);
  }
}
?>
