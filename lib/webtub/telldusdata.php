<?php
class telldusdata {
  private $token, $tokenSecret, $cache;
  public $account, $data = array(), $errorMessage;

  public function __construct($account)
  {
    $this->cache = config::getCache();
    if($account)
    {
      $this->account = $account;
      $this->token = $this->account->token;
      $this->tokenSecret = $this->account->tokenSecret;
      if($this->account->authenticated)
      {
        try
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
        catch(Exception $ex)
        {
          var_dump($ex);
          $this->errorMessage = $ex->getMessage();
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
    if(($cached = $this->cache->getCache($this->cache->getCacheKey('telldusDataListSensors', array(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->token, $this->tokenSecret)))) !== false)
    {
      return $cached;
    }
    $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->token, $this->tokenSecret);
    $params = array(
      'supportedMethods' => constant('TELLDUS_TELLSTICK_TURNON') | constant('TELLDUS_TELLSTICK_TURNON'),
    );
    $response = $consumer->sendRequest(constant('TELLDUS_REQUEST_URI').'/sensors/list', $params, 'GET');
    $body = json_decode($response->getBody());
    if(isset($body->error) && $body->error)
    {
      throw new Exception($body->error);
    }
    else if(!isset($body->sensor))
    {
      throw new Exception("Unexpected result in listSensors");
    }
    else
    {
      $this->cache->setCache($this->cache->getCacheKey('telldusDataListSensors', array(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->token, $this->tokenSecret)), $body->sensor);
      return $body->sensor;
    }
  }

  private function listDevices() {
    if(($cached = $this->cache->getCache($this->cache->getCacheKey('telldusDataListDevices', array(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->token, $this->tokenSecret)))) !== false)
    {
      return $cached;
    }
    $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->token, $this->tokenSecret);
    $params = array(
      'supportedMethods' => constant('TELLDUS_TELLSTICK_TURNON') | constant('TELLDUS_TELLSTICK_TURNON'),
    );
    $response = $consumer->sendRequest(constant('TELLDUS_REQUEST_URI').'/devices/list', $params, 'GET');
    $body = json_decode($response->getBody());
    if(isset($body->error) && $body->error)
    {
      throw new Exception($body->error);
    }
    else if(!isset($body->device))
    {
      throw new Exception("Unexpected result in listDevices");
    }
    else
    {
      $this->cache->setCache($this->cache->getCacheKey('telldusDataListDevices', array(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->token, $this->tokenSecret)), $body->device);
      return $body->device;
    }
  }

  private function getSensor($id) {
    try {
      $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->token, $this->tokenSecret);
      $params = array(
        'id' => $id,
      );
      $response = $consumer->sendRequest(constant('TELLDUS_REQUEST_URI').'/sensor/info', $params, 'GET');
      $body = json_decode($response->getBody());
      if(isset($body->error) && $body->error)
      {
        throw new Exception($body->error);
      }
      else
      {
        return $body;
      }
    } catch (Exception $ex) {
      logger::log('Could not get sensor with id '.$id, ERROR);
      return new stdClass();
    }
  }

  private function getDevice($id) {
    try {
      $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->token, $this->tokenSecret);
      $params = array(
        'id' => $id,
        'supportedMethods' => constant('TELLDUS_TELLSTICK_TURNON') | constant('TELLDUS_TELLSTICK_TURNON'),
      );
      $response = $consumer->sendRequest(constant('TELLDUS_REQUEST_URI').'/device/info', $params, 'GET');
      $body = json_decode($response->getBody());
      if(isset($body->error) && $body->error)
      {
        throw new Exception($body->error);
      }
      else
      {
        return $body;
      }
    } catch (Exception $ex) {
      logger::log('Could not get device with id '.$id, ERROR);
      return new stdClass();
    }
  }

  public function turnOnDevice($id) {
    $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->token, $this->tokenSecret);
    $params = array(
      'id' => $id,
    );
    $response = $consumer->sendRequest(constant('TELLDUS_REQUEST_URI').'/device/turnOn', $params, 'GET');
    $body = json_decode($response->getBody());
    if(isset($body->error) && $body->error)
    {
      throw new Exception($body->error);
    }
    else
    {
      return $body;
    }
  }

  public function turnOffDevice($id) {
    $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->token, $this->tokenSecret);
    $params = array(
      'id' => $id,
    );
    $response = $consumer->sendRequest(constant('TELLDUS_REQUEST_URI').'/device/turnOff', $params, 'GET');
    $body = json_decode($response->getBody());
    if(isset($body->error) && $body->error)
    {
      throw new Exception($body->error);
    }
    else
    {
      return $body;
    }
  }

  private function getUserProfile() {
    $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $this->token, $this->tokenSecret);
    $params = array(
    );
    $response = $consumer->sendRequest(constant('TELLDUS_REQUEST_URI').'/user/profile', $params, 'GET');$body = json_decode($response->getBody());
    if(isset($body->error) && $body->error)
    {
      throw new Exception($body->error);
    }
    else
    {
      return $body;
    }
  }
}
?>
