<?php
class account {
  public $authenticated, $email, $fullname, $settings = array(), $telldusData, $hasMissingRequiredSettings = false;
  
  public function __construct()
  {
    if($this->authenticated = $this->isAuthenticated()) 
    {
      $this->email = $_SESSION['telldus_email'];
      $this->fullname = $_SESSION['telldus_fullname'];      
      $this->setAccount();
      $this->token = $_SESSION['telldus_access_token'];
      $this->tokenSecret = $_SESSION['telldus_access_token_secret'];
      if(!$this->token || !$this->tokenSecret) {
        $this->setTokens();
      }
      $this->settings = $this->listSettings($this->email);
      $this->telldusData = new telldusdata($this);
      foreach($this->settings as $setting) 
      {
        if($setting['required'] && !$setting['value']) {
          $this->hasMissingRequiredSettings = true;
        }
      }
    }
  }
  
  private function isAuthenticated() {
    return 
      isset($_SESSION['telldus_email'])
       && isset($_SESSION['telldus_access_token'])
       && isset($_SESSION['telldus_access_token_secret']) 
       && $_SESSION['telldus_email'] == telldus::testAccountEmail;
  }
  
  public function authenticate() {
    $lightOpenID = new LightOpenID();
    $lightOpenID->identity = 'http://login.telldus.com';
    $lightOpenID->required = array('contact/email', 'namePerson');
    
    if (isset($_GET['openid_mode']) && $_GET['openid_mode'] == 'id_res') {
      if ($lightOpenID->validate()) {
        $data = $lightOpenID->getAttributes();
        $_SESSION['telldus_email'] = $data['contact/email'];
        $_SESSION['telldus_fullname'] = $data['namePerson'];
        $_SESSION['telldus_access_token'] = telldus::token || false;
        $_SESSION['telldus_access_token_secret'] = telldus::tokenSecret || false;
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
    if($account && $account['accessToken'] && $account['accessTokenSecret']) {
      $_SESSION['telldus_access_token'] = $account['accessToken'];
      $_SESSION['telldus_access_token_secret'] = $account['accessTokenSecret'];
    } else {
      $db = new dbMgr();
      $sql = 'INSERT INTO `accounts` VALUES(NULL, ?, ?, NULL, NULL)';
      $db->query($sql, 'ss', $this->email, $this->fullname);
    }
    $db->closeStmt();
    return;
  }
  
  public function setTokens() {   
    $consumer = new HTTP_OAuth_Consumer(telldus::publicKey, telldus::privateKey);
    $consumer->getRequestToken(telldus::requestTokenUrl, 'http://' . $_SERVER['HTTP_HOST'] . '/access-token');
    $_SESSION['token'] = $consumer->getToken();
    $_SESSION['tokenSecret'] = $consumer->getTokenSecret();
    $url = $consumer->getAuthorizeUrl(telldus::authorizeTokenUrl);
    header('Location:'.$url);
  }
  
  public function setAccessTokens() {
    
  }
  
  // List all settings
  public function listSettings($email)
  {
    $list = array();
    $db = new dbMgr();
    $sql = 'SELECT `key`, `name`, `sourceEndpoint`, `value`, `required` FROM `tubSettings` s LEFT OUTER JOIN `tubAccountSettings` a ON s.key = a.settingKey AND  email = ? ORDER BY name';
    $db->query($sql, 's', $email);
    $list = $db->getRowsAsArray();
    $db->closeStmt();
    return $list;
  }
    
  // Update setting in db
  public function updateSetting($email, $key, $value) 
  {
    try 
    {
      $list = array();
      $db = new dbMgr();
      $sql = 'SELECT `settingKey`, `value` FROM `tubAccountSettings` a 
       WHERE email = ?';
      $db->query($sql, 's', $email);
      $list = $db->getRowsAsArray();
      if(count($list) > 0)
      {
        $sql = 'UPDATE `tubAccountSettings` SET value=? WHERE telldusId=? AND settingKey=?';
        $db->query($sql, 'sss', $value, $email, $key);          
      } 
      else 
      {
        $sql = 'INSERT INTO `tubAccountSettings` VALUES (\'\', ?, ?, ?)';
        $db->query($sql, 'sss', $email, $key, $value);                  
      }
      return $true;
    }
    catch (Exception $ex) 
    {
      $errorMessage = $ex->getMessage();  
    }
    return false;
  }
}
?>