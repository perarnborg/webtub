<?php
class telldusdata {
  public $account, $data = array();
  
  public function __construct($account)
  {
//    require_once 'HTTP_OAuth-0.2.3/HTTP/OAuth/Consumer.php';
    $this->account = $account;
    if($this->account->authenticated) 
    {
      $this->data['sensors'] = $this->listSensors();
    }
  }
  
  private function listSensors() {
    $consumer = new HTTP_OAuth_Consumer(telldus::publicKey, telldus::privateKey, $this->account->token, $this->account->tokenSecret);
    $params = array(
      'supportedMethods' => telldus::tellstickTorunOn | telldus::tellstickTorunOff,
    );
    $response = $consumer->sendRequest(telldus::requestUri . '/sensors/list', $params, 'GET');
  }
}
?>