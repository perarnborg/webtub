<?php
class account {
  public $authenticated, $email, $fullname, $settings = array(), $telldusData, $hasMissingRequiredSettings = false, $tubTime;

  public function __construct()
  {
    if($this->authenticated = $this->isAuthenticated())
    {
      $this->email = $_SESSION[webtub::sessionKeyEmail];
      $this->fullname = $_SESSION[webtub::sessionKeyFullname];
      $this->setAccount();
      $this->settings = $this->listSettings();
      foreach($this->settings as $setting)
      {
        if($setting['required'] && !$setting['value']) {
          $this->hasMissingRequiredSettings = true;
        }
      }
      $this->tubTime = $this->getTubTime();
    }
  }

  public function logout()
  {
    if($this->authenticated = $this->isAuthenticated())
    {
      unset($_SESSION[webtub::sessionKeyEmail]);
      unset($_SESSION[webtub::sessionKeyFullname]);
      unset($_SESSION[webtub::sessionKeyAccessToken]);
      unset($_SESSION[webtub::sessionKeyAccessTokenSecret]);
      session_destroy();
    }
  }

  public function authenticate() {
    $lightOpenID = new LightOpenID();
    $lightOpenID->identity = 'http://login.telldus.com';
    $lightOpenID->required = array('contact/email', 'namePerson');
    if (isset($_GET['openid_mode']) && $_GET['openid_mode'] == 'id_res') {
      if ($lightOpenID->validate()) {
        $data = $lightOpenID->getAttributes();
        $_SESSION[webtub::sessionKeyEmail] = $data['contact/email'];
        $_SESSION[webtub::sessionKeyFullname] = $data['namePerson'];
        $_SESSION[webtub::sessionKeyAccessToken] = false;
        $_SESSION[webtub::sessionKeyAccessTokenSecret] = false;
        return true;
      } else {
        return false;
      }
    } else if (isset($_GET['openid_mode']) && $_GET['openid_mode'] == 'cancel') {
      return false;
    } else {
      header('Location: '. $lightOpenID->authUrl(true) );
    }
  }

  public function setAccount() {
    $db = new dbMgr();
    $sql = 'SELECT `accessToken`, `accessTokenSecret` FROM `accounts` a
      WHERE email=?';
    $db->query($sql, 's', $this->email);
    $account = $db->getRow();
    if($account) {
      if($account['accessToken'] && $account['accessTokenSecret']) {
        $_SESSION[webtub::sessionKeyAccessToken] = $account['accessToken'];
        $_SESSION[webtub::sessionKeyAccessTokenSecret] = $account['accessTokenSecret'];
      } else if($_SESSION[webtub::sessionKeyAccessToken] && $_SESSION[webtub::sessionKeyAccessTokenSecret]) {
        $sql = 'UPDATE `accounts` SET accessToken=?, accessTokenSecret=? WHERE email=?';
        $db->query($sql, 'sss', $_SESSION[webtub::sessionKeyAccessToken], $_SESSION[webtub::sessionKeyAccessTokenSecret], $this->email);
      }
    } else {
      $db = new dbMgr();
      $sql = 'INSERT INTO `accounts` VALUES(NULL, ?, ?, NULL, NULL)';
      $db->query($sql, 'ss', $this->email, $this->fullname);
    }
    $db->closeStmt();
    return;
  }

  public function initTelldusData() {
    $this->token = $_SESSION[webtub::sessionKeyAccessToken];
    $this->tokenSecret = $_SESSION[webtub::sessionKeyAccessTokenSecret];
    if(!$this->token || !$this->tokenSecret) {
      $this->setTokens();
    }
    $this->telldusData = new telldusdata($this);

    if(!$this->telldusData->errorMessage)
    {
      $settings = array();
      foreach($this->settings as $setting) {
        if($setting['sourceEndpoint']) {
          $setting['values'] = array();
          if(isset($this->telldusData->data[$setting['sourceEndpoint']])) {
            $valueExistsInValues = false;
            $setting['values'] = $this->telldusData->data[$setting['sourceEndpoint']];
            foreach($setting['values'] as $value)
            {
              if($value->id == $setting['value'])
              {
                $valueExistsInValues = true;
              }
            }
            if(!$valueExistsInValues)
            {
              $setting['value'] = NULL;
              if($setting['required'])
              {
                $this->hasMissingRequiredSettings = true;
              }
            }
          }
        }
        $settings[$setting['key']] = $setting;
      }
      $this->settings = $settings;
      return true;
    }
    else
    {
      return false;
    }
  }

  private function isAuthenticated() {
    return
      isset($_SESSION[webtub::sessionKeyEmail]);
  }

  public function setTokens() {
    $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'));
    $consumer->getRequestToken(constant('TELLDUS_REQUEST_TOKEN'), constant('TELLDUS_BASE_URL') . '/access-token');
    $_SESSION[webtub::sessionKeyRequestToken] = $consumer->getToken();
    $_SESSION[webtub::sessionKeyRequestTokenSecret] = $consumer->getTokenSecret();
    $url = $consumer->getAuthorizeUrl(constant('TELLDUS_AUTHORIZE_TOKEN'));
    header('Location:'.$url);
  }

  public function setAccessTokens() {
    $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $_SESSION[webtub::sessionKeyRequestToken], $_SESSION[webtub::sessionKeyRequestTokenSecret]);
    try {
      $consumer->getAccessToken(constant('TELLDUS_ACCESS_TOKEN'));
      $_SESSION[webtub::sessionKeyAccessToken] = $consumer->getToken();
      $_SESSION[webtub::sessionKeyAccessTokenSecret] = $consumer->getTokenSecret();
      return true;
    }
    catch(Exception $ex)
    {
      mail("dev@perarnborg.se", "WebTub error", "Error setting access token:\n" . $ex->getMessage());
    }
    return false;
  }

  // List all settings
  public function listSettings()
  {
    $list = array();
    $db = new dbMgr();
    $sql = 'SELECT `key`, `name`, `sourceEndpoint`, `value`, `required` FROM `tubSettings` s LEFT OUTER JOIN `tubAccountSettings` a ON s.key = a.settingKey AND  email = ? ORDER BY name';
    $db->query($sql, 's', $this->email);
    foreach($db->getRowsAsArray() as $row)
    {
      $list[$row['key']] = $row;
    }
    $db->closeStmt();
    return $list;
  }

  // Update settings from post
  public function updateSettings($post) {
    foreach($post as $key=>$value) {
      $this->updateSetting($key, $value);
    }
  }

  // Update setting in db
  private function updateSetting($key, $value)
  {
    try
    {
      $list = array();
      $db = new dbMgr();
      $sql = 'SELECT `settingKey`, `value` FROM `tubAccountSettings` a
       WHERE email = ? AND settingKey = ?';
      $db->query($sql, 'ss', $this->email, $key);
      $list = $db->getRowsAsArray();
      if(count($list) > 0)
      {
        $sql = 'UPDATE `tubAccountSettings` SET value=? WHERE email=? AND settingKey=?';
        $db->query($sql, 'sss', $value, $this->email, $key);
      }
      else
      {
        $sql = 'INSERT INTO `tubAccountSettings` VALUES (\'\', ?, ?, ?)';
        $db->query($sql, 'sss', $this->email, $key, $value);
      }
      return true;
    }
    catch (Exception $ex)
    {
      $errorMessage = $ex->getMessage();
    }
    return false;
  }

  // Get next tub time from db
  private function getTubTime()
  {
    try
    {
      $list = array();
      $db = new dbMgr();
      $sql = 'SELECT `id`, `time`, `temp`, `activated`, `deactivated` FROM `tubTimes` t
       WHERE email = ? AND time > ?';
      $db->query($sql, 'si', $this->email, time() - 3600); // Get times that end less are more than an hour ago
      return $db->getRow();
    }
    catch (Exception $ex)
    {
      $errorMessage = $ex->getMessage();
    }
    return false;
  }

  // Update or create tub time in db
  public function updateOrCreateTubTime($time, $temp)
  {
    try
    {
      if($existing = $this->getTubTime())
      {
        $db = new dbMgr();
        $sql = 'UPDATE `tubTimes`
         SET time = ?, temp = ?, activated = 0, deactivated = 0
         WHERE id = ?';
        $db->query($sql, 'idi', $time, $temp, $existing['id']);
      }
      else
      {
        $db = new dbMgr();
        $sql = 'INSERT INTO `tubTimes` VALUES(\'\', ?, ?, ?, 0, 0, ?)';
        $db->query($sql, 'sidi', $this->email, $time, $temp, time());
      }
    }
    catch (Exception $ex)
    {
      $errorMessage = $ex->getMessage();
    }
    return false;
  }

  // Update or create tub time in db
  public function deleteTubTime()
  {
    try
    {
      if($existing = $this->getTubTime())
      {
        if($existing['activated'] && !$existing['deactivated'])
        {
          if(!$this->telldusData)
          {
            $this->initTelldusData();
          }
          $this->telldusData->turnOffDevice($this->settings['tubDeviceId']['value']);
        }
        $db = new dbMgr();
        $sql = 'DELETE FROM `tubTimes`
         WHERE id = ?';
        $db->query($sql, 'i', $existing['id']);
      }
    }
    catch (Exception $ex)
    {
      $errorMessage = $ex->getMessage();
      var_dump($errorMessage);
      die();
    }
    return false;
  }
}
?>
