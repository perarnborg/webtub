<?php

/**
 * timer class
 * Use to time different functions or whole requests
  *
 */

class timer
{
  private $timer = 0;
  private $startTime = 0;
  
  public function __construct()
  {
    
    return $this->startTimer();
  }
  
  public function startTimer()
  {
    $this->startTime = microtime(true);
  }
  
  public function endTimer($return = true)
  {
    $this->timer = microtime(true) - $this->startTime;
    
    if($return)
    {
      return $this->timer;
    }
    else
    {
      echo $this->timer;
    }
  }
}