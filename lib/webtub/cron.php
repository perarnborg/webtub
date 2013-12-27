<?php

class cron
{
  private $tokens, $settings, $telldusData;
  public function __construct($testMode = false)
  {
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
      logger::log("Active tubtime", DEBUG);
      $token = $tokenSecret = $settings = null;
      if($this->validateTubTime($activeTubTime, $token, $tokenSecret, $settings)) {
        logger::log("Valid tubtime", DEBUG);
        $this->telldusData->setTokens($token, $tokenSecret);
        // Check if tub should be turned off
        try
        {
          $currentTubTemp = floatval($this->telldusData->getSensorTemp($settings['tubSensorId']));
          $currentAirTemp = floatval($this->telldusData->getSensorTemp($settings['airSensorId']));
          $tubShouldBeTurnedOff = $this->tubOnOrOff($currentTubTemp, $currentAirTemp, $activeTubTime['temp'], $activeTubTime['time']);
          if($tubShouldBeTurnedOff)
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
          $currentTubTemp = floatval($this->telldusData->getSensorTemp($settings['tubSensorId']));
          $currentAirTemp = floatval($this->telldusData->getSensorTemp($settings['airSensorId']));
          $tubShouldBeTurnedOn = $this->tubOnOrOff($currentTubTemp, $currentAirTemp, $futureInactiveTubTime['temp'], $futureInactiveTubTime['time']);
          if($tubShouldBeTurnedOn)
          {
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

  public function tubOnOrOff($currentTubTemp, $currentAirTemp, $requestedTemp, $requestedTime) {
    $c = 0.0003;
    $Td = 150;
    $secondsInWeek = 7 * 24 * 60 * 60;
    $t = $secondsInWeek / 60;
    $requestedTime = ($requestedTime - time() + $secondsInWeek) / 60;
    $coff = ($currentTubTemp - $currentAirTemp) / exp(-$c * $t);
    $con = ($requestedTemp - $currentAirTemp - $Td) / exp(-$c * $requestedTime);
    $x = -1 / $c * log($Td/($coff - $con));
    return $x < $t;
  }

  private function _deprecated_tubOnOrOff($currentTubTemp, $currentAirTemp, $requestedTemp, $requestedTime) {
    $coolingPerHour = 0.5;
    $warmingPerHour = 2.2;
    $hoursLeft = ($requestedTime - time()) / 3600;
    if($hoursLeft <= 0) {
      return $currentTubTemp < $requestedTemp;
    } else if($hoursLeft < 1) {
      return $currentTubTemp - ($coolingPerHour * $hoursLeft) < $requestedTemp;
    }
    return ($currentTubTemp + ($warmingPerHour * ($hoursLeft - 1)) - ($coolingPerHour * 1) <= $requestedTemp);
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
