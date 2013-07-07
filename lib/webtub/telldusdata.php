<?php
class telldusdata {
  private $token, $tokenSecret;
  public $account, $data = array();
  
  public function __construct($account)
  {
    if($account) 
    {
      $this->account = $account;
      $this->token = $this->account->token;
      $this->tokenSecret = $this->account->tokenSecret;
      if($this->account->authenticated) 
      {
        $this->data['sensors'] = $this->listSensors();
        $this->data['devices'] = $this->listDevices();
        if(isset($this->account->settings['tubSensorId']) && $this->account->settings['tubSensorId']['value'])
        {
          $lastChecked = null;
          $this->data['tubTemp'] = $this->getSensorTemp($this->account->settings['tubSensorId']['value'], $lastChecked);
          $this->data['tubLastChecked'] = $lastChecked;
          $this->data['tubLastCheckedRecently'] = ($lastChecked + 1800) > time();
        }
        if(isset($this->account->settings['tubDeviceId']) && $this->account->settings['tubDeviceId']['value'])
        {
          $this->data['tubStateOn'] = $this->getDeviceState($this->account->settings['tubDeviceId']['value']);          
        }
        if(isset($this->account->settings['airSensorId']) && $this->account->settings['airSensorId']['value'])
        {
          $this->data['airTemp'] = $this->getSensorTemp($this->account->settings['airSensorId']['value']);
        }
      }
    }
  }
  
  public function setTokens($token, $tokenSecret) {
    $this->token = $token;
    $this->tokenSecret = $tokenSecret;
  }
  
  public function getSensorTemp($id, &$lastChecked = null) {
    $tubSensor = $this->getSensor($id);
    if(isset($tubSensor->data))
    {
      $lastChecked = $tubSensor->lastUpdated;
      foreach($tubSensor->data as $data) 
      {
        if($data->name == 'temp')
        {
          return $data->value;              
        }
      }
    }
    return null;
  }
  
  public function getDeviceState($id) {
    $tubDevice = $this->getDevice($id);
    if(isset($tubDevice->state))
    {
      return $tubDevice->state == "1";
    }
    return null;
  }
  
  private function listSensors() {
    $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->token, $this->tokenSecret);
    $params = array(
      'supportedMethods' => constant('TELLDUS_TELLSTICK_TURNON') | constant('TELLDUS_TELLSTICK_TURNON'),
    );
    $response = $consumer->sendRequest(constant('TELLDUS_REQUEST_URI').'/sensors/list', $params, 'GET');
    return json_decode($response->getBody())->sensor;
  }
  
  private function listDevices() {
    $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->token, $this->tokenSecret);
    $params = array(
      'supportedMethods' => constant('TELLDUS_TELLSTICK_TURNON') | constant('TELLDUS_TELLSTICK_TURNON'),
    );
    $response = $consumer->sendRequest(constant('TELLDUS_REQUEST_URI').'/devices/list', $params, 'GET');
    return json_decode($response->getBody())->device;
  }
  
  private function getSensor($id) {
    $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->token, $this->tokenSecret);
    $params = array(
      'id' => $id,
    );
    $response = $consumer->sendRequest(constant('TELLDUS_REQUEST_URI').'/sensor/info', $params, 'GET');
    return json_decode($response->getBody());
  }
  
  private function getDevice($id) {
    $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->token, $this->tokenSecret);
    $params = array(
      'id' => $id,
      'supportedMethods' => constant('TELLDUS_TELLSTICK_TURNON') | constant('TELLDUS_TELLSTICK_TURNON'),
    );
    $response = $consumer->sendRequest(constant('TELLDUS_REQUEST_URI').'/device/info', $params, 'GET');
    return json_decode($response->getBody());
  }
  
  public function turnOnDevice($id) {
    $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->token, $this->tokenSecret);
    $params = array(
      'id' => $id,
    );
    $response = $consumer->sendRequest(constant('TELLDUS_REQUEST_URI').'/device/turnOn', $params, 'GET');
    return json_decode($response->getBody());
  }
  
  public function turnOffDevice($id) {
    $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->token, $this->tokenSecret);
    $params = array(
      'id' => $id,
    );
    $response = $consumer->sendRequest(constant('TELLDUS_REQUEST_URI').'/device/turnOff', $params, 'GET');
    return json_decode($response->getBody());
  }
  
  private function getUserProfile() {
    $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->token, $this->tokenSecret);
    $params = array(
    );
    $response = $consumer->sendRequest(constant('TELLDUS_REQUEST_URI').'/user/profile', $params, 'GET');
    return json_decode($response->getBody());
  }
}
?>