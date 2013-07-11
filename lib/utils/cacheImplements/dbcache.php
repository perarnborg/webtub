<?php
/**
 * db as cache
 * Created on Jul 31, 2012
  */

class dbCache extends aCache
{
  private $isEnabled = true;
  private $dbConn;
  
  public function __construct()
  {
    try {
      $this->dbConn = new dbMgr();
    }
    catch( dbException $e ) {
      logger::log('couldnt connect to db disabling cache!', ALL);
      $this->isEnabled = false;
    }
  }
  
  public function getCache($key)
  {
    if(!$this->isEnabled)
    {
      return false;
    }
    $timeToLive = time()-config::timeToLive;
    $sql = 'SELECT cache.data FROM cache WHERE cache.key = ? AND cache.updateTime > ?';
    try {
      $this->dbConn->query($sql, 'si', $key, $timeToLive);
    } catch (dbException $e) {
      logger::log('Cant select cache from db');
      throw new Exception('Cant select cache from db');
    }
    $row = $this->dbConn->getRow();
    logger::log($row!==false ? 'Cache found' : 'Cache not found', ALL);
    return $row===false?false:$row['data'];
  }
  
  public function setCache($key, $value)
  {
    if(!$this->isEnabled)
    {
      return false;
    }
    $sql = 'INSERT INTO cache (cache.key, cache.data, cache.updateTime) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE cache.data = ?, cache.updateTime = ?';
    try {
      $this->dbConn->query($sql, 'ssisi', $key, $value, time(), $value, time());
      
    } catch (dbException $e) {
      logger::log('Cant insert cache in db');
      throw new Exception('Cant insert cache in db');
      return false;
    }
    return true;
  }
  
  public function deleteCache($key)
  {
    if(!$this->isEnabled)
    {
      return false;
    }
    logger::log('Deleting cache with key: '.$key);
    $timeToLive = time()-config::timeToLive;
    $sql = 'DELETE FROM cache WHERE cache.key = ?';
    try {
      $this->dbConn->query($sql, 's', $key);
    } catch (dbException $e) {
      logger::log('Cant delete cache from db');
      throw new Exception('Cant delete cache from db');
      return false;
    }
    return true;
  }
}
?>
