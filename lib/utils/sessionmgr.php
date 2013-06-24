<?php

/**
  * session manager
 * Singelton
 * starts the session only ones then uses that instance for all the interaction with the session
 */
class sessionMgr {
  
  protected static $_instance;
  
  private function __construct() {
    $this->startSession();
  }
  
  public static function getInstance() {
    if (null === self::$_instance) {
      self::$_instance = new self();
    }
  
    return self::$_instance;
  }
  
  /**
   * startSession - Starts the session handling
   * @param string $sessionID
   * @return boolean
   */
  private function startSession($sessionID=null)
  {
    if(isset($sessionID))
    {
      session_id($sessionId);
    }
    if(function_exists("session_status") && session_status() == 0)
    {
      return session_start();
    }
    else
    {
      return @session_start();
    }
  } 
  
  /**
   * delete a certain key from the session
   * @param string $key
   * @throws Exception - if key not found
   * @return boolean
   */
  public function deleteKey($key)
  {
    if(isset($_SESSION[$key]))
    {
      unset($_SESSION[$key]);
      return true;
    }
    else
    {
      throw new Exception($key .' was not found!');
      return false;
    }
  }
  
  /**
   * Deletes the whole session
   * @param string $sessionId
   * @return boolean
   */
  public function deleteSession($sessionId = null)
  {
    return session_destroy();
  }
  
  /**
   * regenerates the session id to supress hijacking attempts default is to delete the old session.
   * @param optional boolean $deleteOld = true
   * @return boolean
   */
  public function regenerateSessionID($deleteOld = true)
  {
    return session_regenerate_id($deleteOld);
  }
  
  /**
   * sets the value to a session key. Use overwrite if you want to overwrite a already set key with a value.
   * @param string $key
   * @param mixed $value
   * @param boolean $overwrite
   * @return boolean
   */
  public function setSessionVar($key, $value, $overwrite = false)
  {
    if(!isset($_SESSION[$key]) || $overwrite)
    {
      $_SESSION[$key] = $value;
      return true;
    }
    else
    {
      return false;
    }
  }
  
  /**
   * Gets the value of a key
   * @param string $key
   * @return unknown|boolean
   */
  public function getSessionVar($key)
  {
    if(isset($_SESSION[$key]))
    {
      return $_SESSION[$key];
    }
    else
    {
      return false;
    }
  }
}