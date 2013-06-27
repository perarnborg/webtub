<?php
class telldusdata {
  public $account, $data = array();
  
  public function __construct($account)
  {
    $this->account = $account;
    if($this->account->authenticated) 
    {
      $this->data['sensors'] = $this->listSensors();
      $this->data['devices'] = $this->listDevices();
      $this->data['tubSensor'] = $this->getSensor(948635);
      $this->data['userProfile'] = $this->getUserProfile();
    }
  }
  
  private function listSensors() {
    $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->account->token, $this->account->tokenSecret);
    $params = array(
      'supportedMethods' => constant('TELLDUS_TELLSTICK_TURNON') | constant('TELLDUS_TELLSTICK_TURNON'),
    );
    $response = $consumer->sendRequest(constant('TELLDUS_REQUEST_URI').'/sensors/list', $params, 'GET');
    echo( htmlentities($response->getBody()));
  }
  
  private function listDevices() {
    $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->account->token, $this->account->tokenSecret);
    $params = array(
      'supportedMethods' => constant('TELLDUS_TELLSTICK_TURNON') | constant('TELLDUS_TELLSTICK_TURNON'),
    );
    $response = $consumer->sendRequest(constant('TELLDUS_REQUEST_URI').'/devices/list', $params, 'GET');
    echo( htmlentities($response->getBody()));
  }
  
  private function getSensor($id) {
    $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->account->token, $this->account->tokenSecret);
    $params = array(
      'id' => $id
    );
    $response = $consumer->sendRequest(constant('TELLDUS_REQUEST_URI').'/sensor/info', $params, 'GET');
    echo( htmlentities($response->getBody()));
  }
  
  private function getUserProfile() {
    $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->account->token, $this->account->tokenSecret);
    $params = array(
    );
    $response = $consumer->sendRequest(constant('TELLDUS_REQUEST_URI').'/user/profile', $params, 'GET');
    echo( htmlentities($response->getBody()));
  }
}
?>