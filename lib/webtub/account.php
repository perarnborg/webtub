<?php
class account {
  public $authenticated, $telldusId, $email, $fullname, $settings = array();
  
  public function __construct()
  {
    if($this->authenticated = isset($_SESSION['telldus_id']) && isset($_SESSION['telldus_email']) && $_SESSION['telldus_email'] == telldus::testAccountEmail) 
    {
      $this->telldusId = $_SESSION['telldus_id'];
      $this->email = $_SESSION['telldus_email'];
      $this->fullname = $_SESSION['telldus_fullname'];
      $this->settings = $this->listSettings($this->telldusId);
    }
  }
  
  public function authenticate() {
    $lightOpenID = new LightOpenID();
    $lightOpenID->identity = 'http://login.telldus.com';
    $lightOpenID->required = array('contact/email', 'namePerson');
    
    if (isset($_GET['openid_mode']) && $_GET['openid_mode'] == 'id_res') {
      if ($lightOpenID->validate()) {
        $data = $lightOpenID->getAttributes();
        $_SESSION['telldus_id'] = $_GET['openid_identity'];
        $_SESSION['telldus_email'] = $data['contact/email'];
        $_SESSION['telldus_fullname'] = $data['namePerson'];
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
  
  // List all settings
  public function listSettings($telldusId)
  {
    $list = array();
    $db = new dbMgr();
    $sql = 'SELECT `s.key`, `name`, `sourceEndpoint`, `value` FROM `tubSettings` s LEFT OUTER JOIN `tubAccountSettings` a ON s.key = a.key
     WHERE telldusId = ? ORDER BY name';
    $db->query($sql, 'i', $telldusId);
    $list = $db->getRowsAsArray();
    $db->closeStmt();
    return $list;
  }
    
  // Update setting in db
  public function updateSetting($telldusId, $key, $value) 
  {
    try 
    {
      $db = new dbMgr();
      $sql = 'SELECT `key`, `value` FROM `tubAccountSettings` a 
       WHERE telldusId = ?';
      $db->query($sql, 'i', $telldusId);
      $list = $db->getRowsAsArray();
      if(count($list) > 0)
      {
        $sql = 'UPDATE `tubAccountSettings` SET value=? WHERE telldusId=? AND key=?';
        $db->query($sql, 'sis', $value, $telldusId, $key);          
      } 
      else 
      {
        $sql = 'INSERT INTO `tubAccountSettings` VALUES (\'\', ?, ?, ?)';
        $db->query($sql, 'iss', $telldusId, $key, $value);                  
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