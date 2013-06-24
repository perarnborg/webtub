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
      includer::includeFiles(array('header.php', 'default.php', 'footer.php'), 
        array('account' => $account));
      break;
    case 'access-token':
      $account->setAccessTokens();
      break;
    default:
      // Default to File not found
      logger::log('File not found: ' . $_SERVER['REQUEST_URI'], WARNING);
      header("HTTP/1.0 404 Not Found");
      $pageTitle = 'Page not found | ' . webtub::pageTitle;
      includer::includeFiles(array('header.php','404.php','footer.php'), 
        array('httpResponseError' => true, 'pageTitle' => $pageTitle, 'account' => $account));
      break;
  }
}
logger::log('Time to render page: '. $timer->endTimer() .' Request: '. $parameter, ALL);
?>