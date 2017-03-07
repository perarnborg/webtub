<?php

class tub
{

  public static function tubOnOrOff($currentTubTemp, $currentAirTemp, $requestedTemp, $requestedTime, $tubLastChecked, $settings) {
    if($requestedTime < time()) {
      return $currentTubTemp < $requestedTemp;
    }
    $c = self::getSettingsConstant($settings, 'constantC', 0.00029);
    $Td = self::getSettingsConstant($settings, 'constantTd', 125);
    $t = 0; // Let tub last checked be represented by 0
    $requestedTime = ($requestedTime - $tubLastChecked) / 60; // Convert requestedTime to minutes from tub last checked
    $coff = ($currentTubTemp - $currentAirTemp) / exp(-$c * $t);
    $con = ($requestedTemp - $currentAirTemp - $Td) / exp(-$c * $requestedTime);
    $x = -1 / $c * log($Td/($coff - $con));
    return $x < $t;
  }

  public static function getStartTime($tubTime, $telldusData, $settings) {
    if(!$tubTime || !$telldusData) {
      return null;
    }
    if($tubTime['activated'] || $tubTime['deactivated'] || $telldusData->data['tubStateOn']) {
      return null;
    }
    $startTime = null;
    $intervalToCheck = 15 * 60;
    $timeToCheck = $tubTime['time'] - $intervalToCheck;
    while($timeToCheck >= $tubTime['time'] - 24 * 60 * 60) {
      if(!self::tubOnOrOff($telldusData->data['tubTemp'], $telldusData->data['airTemp'], $tubTime['temp'], $tubTime['time'], $timeToCheck, $settings)) {
        $startTime = $timeToCheck + $intervalToCheck;
        break;
      }
      $timeToCheck -= $intervalToCheck;
    }
    return $startTime;
  }

  private static function getSettingsConstant($settings, $key, $defaultValue) {
    if(isset($settings[$key]))
    {
      if(is_array($settings[$key]))
      {
        if($settings[$key]['value'])
        {
          return $settings[$key]['value'];
        }
      }
      else if($settings[$key])
      {
        return floatval($settings[$key]);
      }
    }
    return $defaultValue;
  }

  public static function markTubTimeAsDeactivated($id) {
    $list = array();
    $db = new dbMgr();
    $sql = 'UPDATE `tubTimes` t
    SET deactivated = 1
    WHERE id = ?';
    $db->query($sql, 'i', $id);
  }

  public static function markTubTimeAsActivated($id) {
    $list = array();
    $db = new dbMgr();
    $sql = 'UPDATE `tubTimes` t
    SET activated = 1
    WHERE id = ?';
    $db->query($sql, 'i', $id);
  }

  private static function _deprecated_tubOnOrOff($currentTubTemp, $currentAirTemp, $requestedTemp, $requestedTime, $settings) {
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

}

?>
