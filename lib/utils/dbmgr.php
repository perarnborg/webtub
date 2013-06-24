<?php
class dbMgr
{
  private $conn;
  private $stmt;
  private $result;
  private $sql;
  private $fields;
  private $numRows;
  private $affectedRows;
  private $sqlVars;

  public function __construct()
  {
    $this->connect();
  }

  public function connect()
  {
    if(!$this->conn)
    {
      $this->conn = new mysqli(config::dbHost, config::dbUsername, config::dbPassword, config::dbName);
      if(mysqli_connect_errno())
      {
        logger::log('Couldnt connect to db with reason: '.mysqli_connect_error(), DEBUG);
        throw new dbException('Couldnt connect to db with reason: '.mysqli_connect_error());
        return false;
      }
      return true;
    }
    else
    {
      $this->conn->client_encoding('utf8');
      $this->conn->query( "SET NAMES 'utf8'");
      return true;
    }
  }

  /**
   * query()
   * @param string $sql
   * @param [string $param [$query ...]]
   *
   * query($sql, 'iss', $id, $firstname, $lastname);
   */
  public function query($sql)
  {
    $this->fields = false;
    $this->sql = str_replace(array("\n","\t"), array(' ',''), $sql);
    $this->stmt = $this->conn->prepare($sql);
     
    logger::log('Query sql: '.$this->sql, ALL);
    if($this->stmt)
    {
      $numArgs = func_num_args();
      if($numArgs>1)
      {
        $params = func_get_args();
        array_shift($params);
        $this->sqlVars = $params;
        array_shift($this->sqlVars);
        call_user_func_array(array($this->stmt, 'bind_param'), $this->refValues($params));  
      }

      $this->stmt->execute();
      $this->stmt->store_result();
      $this->numRows = $this->stmt->num_rows;
      $this->affectedRows = $this->stmt->affected_rows;

    }
    else
    {
      logger::log('Couldnt execute query with reason: '.$this->conn->error, DEBUG);
      throw new dbException('Couldnt execute query with reason: '.$this->conn->error);
      return false;
    }
    return true;
  }
  
  public function getRowsAsArray()
  {
    $rows = array();
    while ($row = $this->getRow())
    {
   
      $result = array();
      $keys = array_keys($row);
      foreach($keys as $key) 
      {
        $result[$key] = $row[$key];
      }
      $rows[] = $result;
    }
    return $rows;
  }
  
  public function getRowsInArray()
  {
    $rows = array();
    while ($row = $this->getRow())
    {
      $keys = array_keys($row);
       $rows[] = $row[$keys[0]];
    }
    return $rows;
  }

  
  public function getRow()
  {
    return $this->fetchArray();
  }

  private function fetchArray ()
  {
    $this->result = $this->stmt->result_metadata();

    if($this->numRows)
    {

      if(!$this->fields)
      {
        $fieldNames = $this->result->fetch_fields();  
        foreach($fieldNames as $field) {
          $this->fields[$field->name] = &$out[$field->name];
        }
      }
      
      call_user_func_array(array($this->stmt, 'bind_result'), $this->fields); 
      $res = $this->stmt->fetch();

      return (!$res) ? false : $this->fields;
    }
    return false;
    }
  
  public function lastInsertId() {
    return $this->conn->insert_id;  
  }

  public function close()
  {
    if($this->stmt)
    {
      $this->stmt->free_result();
      $this->stmt->close();
    }
    if($this->conn)
    {
      $this->conn->close();
    }
  }

  public function closeStmt()
  {
    if($this->stmt)
    {
      $this->stmt->free_result();
      $this->stmt->close();
    }
  }

  public function getSql()
  {
    $retSql = $this->sql;
    foreach ($this->sqlVars as $v) {
      $retSql = substr_replace($retSql, $v, strpos($retSql, '?'),1);
    }
    
    return $retSql;
  }

  public function getNumRows()
  {
    return $this->numRows;
  }
  
  public function getAffectedRows()
  {
    return $this->affectedRows;
  }
  
  public function escapeString($string)
  {
    return $this->conn->real_escape_string($string);
  }

  private function refValues($arr){
        if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
        {
            $refs = array();
            foreach($arr as $key => $value)
                $refs[$key] = &$arr[$key];
            return $refs;
        }
        return $arr;
    }
}