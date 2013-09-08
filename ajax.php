<?php 
header("Content-type: text/html; charset=utf-8");
require_once 'HTTP/OAuth/Consumer.php';
require_once 'config/main.php';
//get url parameter and include right file.
$parameter = isset($_GET['p'])?$_GET['p']:'';
//session_destroy();
$parameters = explode('/',$parameter);

$account = new account();
if(!$account->authenticated) 
{
  die();
}

/* AJAX callback pages */
switch($parameters[0])
{ 
  case 'current':
    $account->initTelldusData();
    $current = array(
      'tubTemp' => isset($account->telldusData->data['tubTemp']) ? $account->telldusData->data['tubTemp'] : false,
      'tubStateOn' => isset($account->telldusData->data['tubStateOn']) ? $account->telldusData->data['tubStateOn'] : null,
      'airTemp' => isset($account->telldusData->data['airTemp']) ? $account->telldusData->data['airTemp'] : false,
      'lastChecked' => date("H:i", $account->telldusData->data['tubLastChecked']),
      'lastCheckedRecently' => $account->telldusData->data['tubLastCheckedRecently'],
    );
    echo json_encode($current);
    break;
  case 'turn-on':
    $account->initTelldusData();
    $account->initTelldusData();
    $deviceId = $account->settings['tubDeviceId']['value'];
    $account->telldusData->turnOnDevice($deviceId);
    echo json_encode(true);
    break;
  case 'turn-off':
    $account->initTelldusData();
    $account->initTelldusData();
    $deviceId = $account->settings['tubDeviceId']['value'];
    $account->telldusData->turnOffDevice($deviceId);
    echo json_encode(true);
    break;
}
?>