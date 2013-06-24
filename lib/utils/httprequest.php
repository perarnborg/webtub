<?php
/**
 *
 * httpRequest is used to collect data with Curl
 *
 */
class httpRequest
{
  private $url;
  private $userAgent;
  private $cache;
  private $cacheKey;
  private $noCache;
  private $contenttype;
  
  /**
   *
   * Sets the request url
   * @param string $url, boolean $noCache, string $userAgent
   */
  public function __construct($url, $noCache = false, $userAgent = '')
  {
    $this->url = $url;
    $this->userAgent = empty($userAgent)?config::userAgent:$userAgent;
    $this->noCache = $noCache;
    $this->content_type = '';
    if(!$this->noCache)
    {
      $this->cache = config::getCache();
      $this->cacheKey = $this->cache->getCacheKey('httpRequest', $url);
    }
  }

  // This function makes the request and returns the response as a string. I an error is occured the message is set to the outpu variable errorMessage.
  public function getResponse(&$httpCode)
  {
    try {
      $response = $this->noCache ? false : $this->cache->getCache($this->cacheKey);
      if($response === false)
      {
        logger::log('getResponse: ' . $this->url, DEBUG);
        $ch = curl_init();
        // Set URL to download
        curl_setopt($ch, CURLOPT_URL, $this->url);
        // Set a referer
        //curl_setopt($ch, CURLOPT_REFERER, "http://" . $_SERVER['HTTP_HOST']);
        // User agent
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        // Include header in result? (0 = yes, 1 = no)
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // Should cURL return or print out the data? (true = return, false = print)
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Disable ssl verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        //curl follow location
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        // Download the given URL into cache variable
        $response = curl_exec($ch);
        $info = curl_getinfo($ch); 
        $this->content_type = $info['content_type'];
        
        if(!$response)
        {
          logger::log('Couldn\'t make request: '.$info['http_code']);
          throw new Exception('Could not make request!', $info['http_code']);
        }
        if( !$this->noCache )
        {
          $this->cache->setCache($this->cacheKey, $response);
        }
        return $response;
      }
    }
    catch(Exception $ex) {
      $httpCode = $ex->getCode();
    }
    if(isset($info['http_code']))
    {
      $httpCode = $info['http_code'];
    }
    return $response;
  }
  
  public function checkxml(){
  
    if($this->content_type == 'text/xml'){
  
      return true;
    }else{
  
      return false;
    }
  }
  
}