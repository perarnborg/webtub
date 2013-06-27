<?php
class account {
  public $authenticated, $email, $fullname, $settings = array(), $telldusData, $hasMissingRequiredSettings = false;
  
  public function __construct()
  {
    if($this->authenticated = $this->isAuthenticated()) 
    {
      $this->email = $_SESSION[webtub::sessionKeyEmail];
      $this->fullname = $_SESSION[webtub::sessionKeyFullname];      
      $this->setAccount();
      $this->settings = $this->listSettings($this->email);
      foreach($this->settings as $setting) 
      {
        if($setting['required'] && !$setting['value']) {
          $this->hasMissingRequiredSettings = true;
        }
      }
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
        $db->query($sql, 'sss', $_SESSION['telldus_access_token'], $_SESSION['telldus_access_token_secret'], $this->email);
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
    var_dump($_SESSION);
    $consumer = new HTTP_OAuth_Consumer(constant('TELLDUS_PUBLIC_KEY'), constant('TELLDUS_PRIVATE_KEY'), $_SESSION[webtub::sessionKeyRequestToken], $_SESSION[webtub::sessionKeyRequestTokenSecret]);
    try {
      $consumer->getAccessToken(constant('TELLDUS_ACCESS_TOKEN'));
      $_SESSION[webtub::sessionKeyAccessToken] = $consumer->getToken();
      $_SESSION[webtub::sessionKeyAccessTokenSecret] = $consumer->getTokenSecret();
      var_dump($_SESSION);      
    }
    catch(Exception $ex)
    {
      var_dump($ex);
    }
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