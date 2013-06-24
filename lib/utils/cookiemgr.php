<?php

/**
 * cookie manager
* Singelton
* starts the cookie only ones then uses that instance for all the interaction with the cookie
*/
class cookieMgr {

  protected static $_instance;
  private $cookie;

  private function __construct() {
    $this->startCookie();
  }

  public static function getInstance() {
    if (null === self::$_instance) {
      self::$_instance = new self();
    }

    return self::$_instance;
  }

  /**
   * startcookie - Starts the cookie handling
   * @param string $cookieID
   * @return boolean
   */
  private function startCookie()
  {
    
    if(isset($_COOKIE[config::COOKIE_NAME]))
    {
      $this->cookie = $_COOKIE[config::COOKIE_NAME];
      
    }
    else
    {
      setcookie(config::COOKIE_NAME, "asd", time()+60*60*24*180, '/'); // Remove this to work on all domains: , 
    }
  }

  /**
   * delete a certain key from the cookie
   * @param string $key
   * @throws Exception - if key not found
   * @return boolean
   */
  public function deleteKey($key)
  {
    if(isset($_COOKIE[config::COOKIE_NAME]))
    {
      $this->cookie = json_decode($_COOKIE[config::COOKIE_NAME]);
      if(isset($this->cookie[$key]))
      {
        unset($this->cookie[$key]);
        setcookie(config::COOKIE_NAME, json_encode($this->cookie));
        return true;
      }
    }
    throw new Exception($key .' was not found!');
    return false;
  }

  /**
   * Deletes the whole cookie
   * @param string $cookieId
   * @return boolean
   */
  public function deleteCookie($cookieName = config::COOKIE_NAME)
  {
    return setcookie($name,null,time() - 3600*24);
  }

  /**
   * sets the value to a cookie key
   * @param string $key
   * @param x $value
   * @throws Exception if key is already set
   * @return boolean
   */
  public function setCookieVar($key, $value)
  {
    if(isset($_COOKIE[config::COOKIE_NAME]))
    {
      $this->cookie = json_decode($_COOKIE[config::COOKIE_NAME]);
      $this->cookie->$key = $value;
      setcookie(config::COOKIE_NAME, json_encode($this->cookie));
      return true;
    }
  }

  /**
   * Gets the value of a key
   * @param string $key
   * @throws Exception
   * @return unknown|boolean
   */
  public function getCookieVar($key)
  {
    if(isset($_COOKIE[config::COOKIE_NAME]))
    {
      $this->cookie = json_decode($_COOKIE[config::COOKIE_NAME]);
      var_dump($_COOKIE);
      if(isset($this->cookie->$key))
      {
        return $this->cookie->$key;
      }
    }
    return false;
  }
}