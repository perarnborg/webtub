<?php

class cron
{
  private $tokens, $settings, $telldusData;
  public function __construct()
  {
    $this->settings = $this->listUserSettings();
    $this->tokens = $this->listUserTokens();
    $this->telldusData = new telldusData(false);
  }
  
  public function checkTubs() {
    // Check activated tubs
    logger::log("Check for tubs with cron", ALL);
    $activeTubTimes = $this->listActiveTubTimes();
    foreach($activeTubTimes as $activeTubTime)
    {
      logger::log("Active tubtime", DEBUG);
      $token = $tokenSecret = $settings = null;
      if($this->validateTubTime($activeTubTime, $token, $tokenSecret, $settings)) {
        logger::log("Valid tubtime", DEBUG);
        $this->telldusData->setTokens($token, $tokenSecret);
        // Check if tub should be turned off
        try
        {
          $tubShouldBeTurnedOff = null;
          $currentTemp = floatval($this->telldusData->getSensorTemp($settings['tubSensorId']));
          $coolingPerHour = 0.5;
          $hoursLeft = ($activeTubTime['time'] - time()) / 3600;
          logger::log("Hours left: " . $hoursLeft . ". Current temp: " . $currentTemp . ". Wanted temp: " . $activeTubTime['temp'], DEBUG);
          if($currentTemp - ($coolingPerHour * $hoursLeft) > $activeTubTime['temp'])
          {
            $tubShouldBeTurnedOff = true;
            $response = $this->telldusData->turnOffDevice($settings['tubDeviceId']);
            if(isset($response->status) && $response->status == 'success')
            {
              $this->markTubTimeAsDeactivated($activeTubTime['id']);
              logger::log('Successfully turned off device '.$settings['tubDeviceId'], DEBUG);
            }
            else 
            {
              logger::log('Failed to turned off device '.$settings['tubDeviceId'], WARNING);              
            }
          }
        }
        catch(Exception $ex)
        {
          mail("kontakt@perarnborg.se", "Cron error in webtub", "Error checking tub time. Should be turned off: " . $tubShouldBeTurnedOff . "\n\nTub time: " . var_export($activeTubTime, true) . "\n\nError message: " . $ex->getMessage());
        }
      }
    }
    // Check tubs to be activated
    $futureInactiveTubTimes = $this->listFutureInactiveTubTimes();
    foreach($futureInactiveTubTimes as $futureInactiveTubTime)
    {
      logger::log("Inactive tubtime", DEBUG);
      $token = $tokenSecret = $settings = null;
      if($this->validateTubTime($futureInactiveTubTime, $token, $tokenSecret, $settings)) {
        logger::log("Valid tubtime", DEBUG);
        $this->telldusData->setTokens($token, $tokenSecret);
        // Check if tub should be turned on
        try
        {
          $tubShouldBeTurnedOn = null;
          $currentTemp = floatval($this->telldusData->getSensorTemp($settings['tubSensorId']));
          $coolingPerHour = 0.5;
          $warmingPerHour = 2.5;
          $hoursLeft = ($futureInactiveTubTime['time'] - time()) / 3600;
          logger::log("Hours left: " . $hoursLeft . ". Current temp: " . $currentTemp . ". Wanted temp: " . $futureInactiveTubTime['temp'], DEBUG);
          logger::log("If now: " . ($currentTemp + ($warmingPerHour * ($hoursLeft - 0.5)) - ($coolingPerHour * 0.5)), DEBUG);
          if($currentTemp + ($warmingPerHour * ($hoursLeft + 1)) - ($coolingPerHour * 1) < $futureInactiveTubTime['temp'])
          {
            $tubShouldBeTurnedOn = true;
            $response = $this->telldusData->turnOnDevice($settings['tubDeviceId']);
            if(isset($response->status) && $response->status == 'success')
            {
              $this->markTubTimeAsActivated($futureInactiveTubTime['id']);
              logger::log('Successfully turned on device '.$futureInactiveTubTime['id'], DEBUG);
            }
            else 
            {
              logger::log('Failed to turn on device '.$futureInactiveTubTime['id'], WARNING);              
            }
          }
        }
        catch(Exception $ex)
        {
          mail("kontakt@perarnborg.se", "Cron error in webtub", "Error checking tub time. Should be turned n: " . $tubShouldBeTurnedOn . "\n\nTub time: " . var_export($futureInactiveTubTime, true) . "\n\nError message: " . $ex->getMessage());
        }
      }
    }
  }
  
  private function validateTubTime($tubTime, &$token, &$tokenSecret, &$settings) {
    // Check that user has tokens in db
    if(isset($this->tokens[$tubTime['email']]['token']) && $this->tokens[$tubTime['email']]['token'])
    {
      $token = $this->tokens[$tubTime['email']]['token'];
    }
    if(isset($this->tokens[$tubTime['email']]['tokenSecret']) && $this->tokens[$tubTime['email']]['tokenSecret'])
    {
      $tokenSecret = $this->tokens[$tubTime['email']]['tokenSecret'];
    }
    if(isset($this->settings[$tubTime['email']]) && $this->settings[$tubTime['email']])
    {
      $settings = $this->settings[$tubTime['email']];
    }
    return $token && $tokenSecret && $settings['tubSensorId'] && $settings['tubDeviceId'];
  }
  
  private function listActiveTubTimes() {
    $list = array();
    $db = new dbMgr();
    $sql = 'SELECT `id`, `email`, `time`, `temp` FROM `tubTimes` t 
    WHERE activated = 1 AND deactivated = 0';
    $db->query($sql);
    return $db->getRowsAsArray();
  }
  
  private function listFutureInactiveTubTimes() {
    $list = array();
    $db = new dbMgr();
    $sql = 'SELECT `id`, `email`, `time`, `temp` FROM `tubTimes` t 
    WHERE activated = 0';
    $db->query($sql);
    return $db->getRowsAsArray();
  }
  
  private function markTubTimeAsDeactivated($id) {
    $list = array();
    $db = new dbMgr();
    $sql = 'UPDATE `tubTimes` t 
    SET deactivated = 1
    WHERE id = ?';
    $db->query($sql, 'i', $id);
  }
  
  private function markTubTimeAsActivated($id) {
    $list = array();
    $db = new dbMgr();
    $sql = 'UPDATE `tubTimes` t 
    SET activated = 1
    WHERE id = ?';
    $db->query($sql, 'i', $id);
  }
  
  private function listUserSettings() {
    $settings = array();
    $db = new dbMgr();
    $sql = 'SELECT `email`, `settingKey`, `value` FROM  `tubAccountSettings` a';
    $db->query($sql);
    foreach($db->getRowsAsArray() as $row)
    {
      if(!isset($settings[$row['email']]))
      {
        $settings[$row['email']] = array();
      }
      $settings[$row['email']][$row['settingKey']] = $row['value'];
    }
    $db->closeStmt();
    return $settings;    
  }
  
  private function listUserTokens() {
    $tokens = array();
    $db = new dbMgr();
    $sql = 'SELECT `email`, `accessToken`, `accessTokenSecret` FROM  `accounts` a';
    $db->query($sql);
    foreach($db->getRowsAsArray() as $row)
    {
      if(!isset($tokens[$row['email']]))
      {
        $tokens[$row['email']] = array();
      }
      $tokens[$row['email']]['token'] = $row['accessToken'];
      $tokens[$row['email']]['tokenSecret'] = $row['accessTokenSecret'];
    }
    $db->closeStmt();
    return $tokens;    
  }
}