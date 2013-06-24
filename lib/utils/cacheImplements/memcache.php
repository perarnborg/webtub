<?php
/**
* Using memcached as caching
* @Author perarnborg
*/

class memCache extends aCache
{
  private $isEnabled = true;
  private $m = null;

  public function __construct()
  {
    if($this->m == null)
    {
      $this->m = new Memcached();
    }
    
    $this->m->setOption(Memcached::OPT_BINARY_PROTOCOL,true);
    
    $servers = config::memcacheServers();
    
    //check if servers are added otherwise add them. we dont want to add server already added.
    $diff = utils::arrayRecursiveDiff(config::memcacheServers(), $this->m->getServerList());
    
    if(count($diff) > 0)
    {
      $this->m->addServers($diff);
    }
  }

  public function getCache($key)
  {
    $value = $this->m->get($key);
    if(!$value)
    {
      return false;
    }
    return json_decode($value);
  }

  public function setCache($key, $value)
  {
    if(!$this->m->add($key, json_encode($value), config::timeToLive))
    {
      return false;
    }
    return true;
  }

  public function deleteCache($key)
  {
    if(!$this->m->delete($key))
    {
      return false;
    }
    return true;
  }
  
  public function flushMemcache()
  {
    $this->m->flush();
  }
}
?>
