<?php

/**
 * 
 * cache is used to handle application cache. Temporarliy written with a basic file on disk
 *
 */
 
class fileCache extends aCache
{
  private $isEnabled = true;
  
  public function __construct()
  {
    if(!is_dir(config::cacheDir()))
    {
      if(!mkdir(config::cacheDir()))
      {
        logger::log('Can\'t create cache dir: disable cache.', ALL);
        $this->isEnabled = false;
      }
    }
  }
  
  public function getCache($key, $timeToLive = null)
  {
    $timeToLive = isset($timeToLive) ? $timeToLive : config::timeToLive;
    if(!$this->isEnabled)
    {
      return false;
    }
    logger::log('Get cache: ' . $key, ALL);
    $isFound = false;
    $value = false;
    $cachefile = config::cacheDir().'/._' . $key;
    // Read cachfile content if it exists
    if(file_exists($cachefile) && filemtime($cachefile) + $timeToLive > time()){
      $fh = fopen($cachefile, 'r');
      if(!$fh) {
        throw new Exception('Can not open cache file');  
      }
      $value = json_decode(fread($fh, filesize($cachefile)));
      $isFound = true;
      fclose($fh);
    }
    logger::log($isFound ? 'Cache found' : 'Cache not found', ALL);
    
    return $value;
  }
  
  public function setCache($key, $value) 
  {
    if(!$this->isEnabled)
    {
      return false;
    }
    $cachefile = config::cacheDir().'/._' . $key;
    $fh = fopen($cachefile, 'w');
    if(!$fh) {
      throw new Exception('Can not write cache file');  
    }
    fwrite($fh, json_encode($value));
    fclose($fh);
    chmod($cachefile, 0777);
  }
  
  public function deleteCache($key)
  {
    if(!$this->isEnabled)
    {
      return false;
    }
    
    $value = false;
    $cachefile = config::cacheDir().'/._' . $key;
    
    logger::log('Deleting cache with key: '.$key);
    if(file_exists($cachefile) && filemtime($cachefile) + config::timeToLive > time()){
      $value = unlink($cachefile);
    }
    if(!$value)
    {
      logger::log('Cant delete cache from filesystem');
      throw new Exception('Cant delete cache from filesystem');
      return false;
    }
    return true;
  }
  
  public function flushCache()
  {
    if(!$this->isEnabled)
    {
      return false;
    }
  
    $files = glob('path/to/temp/{,.}*', GLOB_BRACE);
    foreach($files as $file)
    {
      if(is_file($file))
      {
        unlink($file);
      }
    }
    return true;
  }
}