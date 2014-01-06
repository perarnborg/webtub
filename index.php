<?php
header("Content-type: text/html; charset=utf-8");
require_once 'HTTP/OAuth/Consumer.php';
require_once 'config/main.php';
$timer = new timer();
//get url parameter and include right file.
$parameter = isset($_GET['p'])?$_GET['p']:'';
logger::log('Start of request: '.$parameter, ALL);

$account = new account();
if(!$account->authenticated)
{
  if(!$account->authenticate())
  {
    includer::includeFiles(array('header.php', 'login.php', 'footer.php'),
      array('account' => $account));
  } else {
    header('Location: /');
  }
}
else
{

  $parameters = explode('/',$parameter);

  switch($parameters[0])
  {
    case '':
      if($account->initTelldusData())
      {
        includer::includeFiles(array('header.php', 'default.php', 'footer.php'),
          array('account' => $account));
      }
      else
      {
        logger::log("Telldus integration error. " . $account->telldusData->errorMessage, ERROR);
        includer::includeFiles(array('header.php', '500.php', 'footer.php'),
          array('httpResponseError' => true));
      }
      break;
    case 'access-token':
      if($account->setAccessTokens()) {
        header('Location: /');
      } else {
        includer::includeFiles(array('header.php', 'login.php', 'footer.php'),
          array('account' => $account));
      }
      break;
    case 'test':
      if($account->initTelldusData()) {
        $turnOn = null;
        if($_POST) {
          require_once config::libDir().'/webtub/cron.php';
          $time = 0;
          if($_POST['date'] && $_POST['time']) {
            $time = strtotime($_POST['date'] . ' ' . $_POST['time']);
          }
          $temp =  floatval(str_replace(',','.',$_POST['temp']));
          $cron = new cron(true);
          $turnOn = $cron->tubOnOrOff($account->telldusData->data['tubTemp'], $account->telldusData->data['airTemp'], $temp, $time, $account->telldusData->data['tubLastChecked'], $account->settings);
        }
        includer::includeFiles(array('header.php', 'test.php', 'footer.php'),
          array('account' => $account, 'turnOn' => $turnOn));
      }
      break;
    case 'post':
      $pageExists = false;
      switch($parameters[1])
      {
        case 'settings':
          $account->updateSettings($_POST);
          header('Location: /');
          $pageExists = true;
          break;
        case 'tubtime':
          if(isset($_POST['delete']))
          {
            $account->deleteTubTime();
          }
          else
          {
            $time = 0;
            if($_POST['date'] && $_POST['time']) {
              $time = strtotime($_POST['date'] . ' ' . $_POST['time']);
            } else if($_POST['datetime']){
              $time = strtotime(str_replace('T', ' ', $_POST['datetime']));
            }
            $temp =  floatval(str_replace(',','.',$_POST['temp']));
            if($temp <= 20 || $temp > 40) {
              $temp = false;
            }
            if($time && $temp) {
              $account->updateOrCreateTubTime($time, $temp);
              header('Location: /');
              $pageExists = true;
            }
          }
          break;
        case 'turn-on':
          $account->initTelldusData();
          $deviceId = $account->settings['tubDeviceId']['value'];
          $account->telldusData->turnOnDevice($deviceId);
          header('Location: /');
          $pageExists = true;
          break;
        case 'turn-off':
          $account->initTelldusData();
          $deviceId = $account->settings['tubDeviceId']['value'];
          $account->telldusData->turnOffDevice($deviceId);
          header('Location: /');
          $pageExists = true;
          break;
      }
      if($pageExists)
      {
        break;
      }
    case 'logout':
      $account->logout();
      header('Location: /');
      break;
    default:
      // Default to File not found
      logger::log('File not found: ' . $_SERVER['REQUEST_URI'], WARNING);
      header("HTTP/1.0 404 Not Found");
      $pageTitle = 'Page not found | ' . webtub::pageTitle;
      includer::includeFiles(array('header.php','404.php','footer.php'),
        array('httpResponseError' => true, 'pageTitle' => $pageTitle));
      break;
  }
}
logger::log('Time to render page: '. $timer->endTimer() .' Request: '. $parameter, ALL);
?>
