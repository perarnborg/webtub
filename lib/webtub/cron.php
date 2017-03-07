<?php

class cron
{
  private $tokens, $settings, $telldusData;
  public function __construct($testMode = false)
  {
    $this->settings = array();
    if(!$testMode) {
      $this->settings = $this->listUserSettings();
      $this->tokens = $this->listUserTokens();
      $this->telldusData = new telldusData(false);
    }
  }

  public function checkTubs() {
    // Check activated tubs
    logger::log("Check for tubs with cron", ALL);
    $activeTubTimes = $this->listActiveTubTimes();
    foreach($activeTubTimes as $activeTubTime)
    {
      logger::log("Active tubtime " . $activeTubTime['id'], DEBUG);
      $token = $tokenSecret = $settings = null;
      if($this->validateTubTime($activeTubTime, $token, $tokenSecret, $settings)) {
        $this->telldusData->setTokens($token, $tokenSecret);
        $tubIsTurnedOn = $this->telldusData->getDeviceState($settings['tubDeviceId']);
        $keepTimeAliveUntil = isset($settings['keepWarmFor']) && $settings['keepWarmFor'] ? $activeTubTime['time'] + ((int)$settings['keepWarmFor'] * 60) : ($activeTubTime['time'] + 3600);
        // Check if tub time should be put to sleep (if keep time alive has run out...)
        if($keepTimeAliveUntil <= time())
        {
          if($tubIsTurnedOn)
          {
            try {
              $this->turnTubOnOrOff($settings['tubDeviceId'], false);
            } catch(Exception $ex) {}
          }
          tub::markTubTimeAsDeactivated($activeTubTime['id']);
          logger::log('Let tub time ' . $activeTubTime['id'] . ' sleep (device id '.$settings['tubDeviceId'] . ')', DEBUG);
        }
        else
        {
          // Check if tub should be turned on or off
          try
          {
            $tubLastChecked = null;
            $currentTubTemp = floatval($this->telldusData->getSensorTemp($settings['tubSensorId'], $tubLastChecked));
            $currentAirTemp = floatval($this->telldusData->getSensorTemp($settings['airSensorId']));
            $tubShouldBeTurnedOn = tub::tubOnOrOff($currentTubTemp, $currentAirTemp, $activeTubTime['temp'], $activeTubTime['time'], $tubLastChecked, $settings);
            logger::log("Active tubtime should be turned on: " . $tubShouldBeTurnedOn . ". Is turned on: " . $tubIsTurnedOn, DEBUG);
            if($tubShouldBeTurnedOn != $tubIsTurnedOn) {
              $this->turnTubOnOrOff($settings['tubDeviceId'], $tubShouldBeTurnedOn);
            }
          }
          catch(Exception $ex)
          {
            mail("kontakt@perarnborg.se", "Cron error in webtub", "Error checking tub time. Should be turned on: " . $tubShouldBeTurnedOn . "\n\nTub time: " . var_export($activeTubTime, true) . "\n\nError message: " . $ex->getMessage());
          }
        }
      }
    }
    // Check tubs to be activated
    $futureInactiveTubTimes = $this->listFutureInactiveTubTimes();
    foreach($futureInactiveTubTimes as $futureInactiveTubTime)
    {
      logger::log("Inactive tubtime " . $futureInactiveTubTime['id'], DEBUG);
      $token = $tokenSecret = $settings = null;
      if($this->validateTubTime($futureInactiveTubTime, $token, $tokenSecret, $settings)) {
        $this->telldusData->setTokens($token, $tokenSecret);
        // Check if tub should be turned on
        try
        {
          $tubLastChecked = null;
          $currentTubTemp = floatval($this->telldusData->getSensorTemp($settings['tubSensorId'], $tubLastChecked));
          $currentAirTemp = floatval($this->telldusData->getSensorTemp($settings['airSensorId']));
          $tubShouldBeTurnedOn = tub::tubOnOrOff($currentTubTemp, $currentAirTemp, $futureInactiveTubTime['temp'], $futureInactiveTubTime['time'], $tubLastChecked, $settings);
          logger::log("Inactive tubtime should be turned on: " . ($tubShouldBeTurnedOn ? 'true' : 'false'), DEBUG);
          if($tubShouldBeTurnedOn)
          {
            if($this->turnTubOnOrOff($settings['tubDeviceId'], true))
            {
              tub::markTubTimeAsActivated($futureInactiveTubTime['id']);
            }
          }
        }
        catch(Exception $ex)
        {
          mail("kontakt@perarnborg.se", "Cron error in webtub", "Error checking tub time. Should be turned on: " . $tubShouldBeTurnedOn . "\n\nTub time: " . var_export($futureInactiveTubTime, true) . "\n\nError message: " . $ex->getMessage());
        }
      }
    }
  }

  private function turnTubOnOrOff($id, $turnOn) {
    $response = null;
    if($turnOn)
    {
      $response = $this->telldusData->turnOnDevice($id);
    }
    else
    {
      $response = $this->telldusData->turnOffDevice($id);
    }
    if(isset($response->status) && $response->status == 'success')
    {
      logger::log('Successfully turned ' . ($turnOn ? 'on' : 'off') . ' device '.$id, DEBUG);
      return true;
    }
    else
    {
      logger::log('Failed to turn  ' . ($turnOn ? 'on' : 'off') . '  device '.$id, WARNING);
      return false;
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
