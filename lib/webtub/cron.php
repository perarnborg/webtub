<?php
/**
 * 
 * Cron processed API requests from cron job
 * @author martinmccarthy
 *
 */

class cron
{
  private $fromDate;
  private $toDate;
 
  
  public function __construct()
  {
    $this->fromDate =  date("Y-m-d H:i:s");
    $this->toDate =  date("Y-m-d H:i:s");
   
  }
  
   /**
   *   SearchRec
   */
  public function SearchRec()
  {

    $recommendations = array();
    $categories = array();
    $errorMessage = NULL;
    $activeApis = $this->getActiveApis(0);
    $searchApis = webtub::searchApis();
      
    logger::log('search the apis');
    $searchApis = array_intersect_key($searchApis, array_flip($activeApis));
    
    // update cron table to allow crossearch instead of search
    if(empty($searchApis)){
      $this->changeSearch(2); 
      
    }
    
    //params is the terms we should search for
    foreach($searchApis as $api=>$params)
    {
      logger::log('creating the api! '.$api);
        
      // get cron settings for API
      $crawler = $this->crondb($api);
         
        $offset = ($crawler->limit - $crawler->used );
   
        // reset if destinations have all been parsed
        if ($crawler->dest_id >= $crawler->dest_tot)
        {
          $this->cronupdate($api,0,null,0,'CYCLE Complete',1);
          //$this->emailcroninfo("Alert! webtub {$api} API - CYCLE complete","Search API {$crawler->dest_id} : {$crawler->dest_tot} ");
        }
        else
        {
          // get Destinations to parse
          $limit = ($crawler->limit - $crawler->used );
          $destinationSearch = new destinationSearch();
          
          $destinations = $destinationSearch->getAllDestinationList( $crawler->dest_id,$offset);
          
          if($destinations){
          
            foreach($destinations as $destination){
                
              $search = new $api();
              foreach($params as $pkey => $param)
              {
                if( $limit > 0 ){
          
                  // decrement limit & increment used for each call
                  //logger::log('Cron Run! '.$api);
                  $limit = $limit - 1;
                  $crawler->used = $crawler->used + 1;
                  $results = $search->search($destination, $param, $this->fromDate, $this->toDate);
                  
                  if($results && !empty($results)){ 
                    foreach ($results as $result)
                    {
                      //logger::log('saving the result');
                      $data = '';
                      $data[$search->getSource()] = $result;
             
                      $recommendations = new recommendation($destination['id'],$pkey, $result['title']);
                      $recommendations->startdate = isset($result['startdate'])?$result['startdate']:null;
                      $recommendations->enddate = isset($result['enddate'])?$result['enddate']:null;
                      $recommendations->score = isset($result['score'])?$result['score']:0;
                      $recommendations->lat = isset($result['lat'])?$result['lat']:null;
                      $recommendations->lng = isset($result['lng'])?$result['lng']:null;
                      $recommendations->source = $search->getSource();
                      
                      //$recommendations->saveRecommendation( $data , $api);
                      $recommendations->NewSaveRecommendation( $data , $api);
                      
                      $dest_id = $destination['id'];
                        
                    }
                  }
                  else
                  {
                    // No Result
                    $dest_id = $destination['id'];
                      
                    //var_dump($dest_id);
                  
          
                  }
                }
                else
                {
                  logger::log('Cron Limit Reached! '.$api);
                  $dest_id = isset($dest_id )?$dest_id:$crawler->dest_id;
                }
              }
            }
          }else{
              $dest_id = 0;
          }  
           
          $this->cronupdate($api,$dest_id,null,$crawler->used,'Cron Run');
        }  
      
     
    }
  }
  
  /**
   *   crossSearchRec
   */
  public function crossSearchRec()
  {
      //$rec = new recommendationSearch($this->date,$this->tomor);
      //$data = $rec->searchForApiData();
      //var_dump($data);
      //var_dump($dest);
      
    $crossApis = webtub::crossApis();
    $activeApis = $this->getActiveApis(2);
    
    logger::log('search the apis');
    $crossApis = array_intersect_key(array_flip($crossApis), array_flip($activeApis));
    $crossApis = array_flip($crossApis);
    logger::log('search the apis for a specific ApiData');

    
    // update cron table to finished cycle to prevent further processing
    if(empty($crossApis)){
      $this->changeSearch(3);
      
      //flush the file cache before it gets rebuilt
      $fileCache = new fileCache();
      $fileCache->flushCache();
    }
    
    //params is the terms we should search for
    foreach($crossApis as $k=>$api)
    {
      
      logger::log('creating the api! '.$api);
      // get cron settings for API
      $crawler = $this->crondb($api);
     
        echo $crawler->lock;
        $offset = ($crawler->limit - $crawler->used );
        //$rec_id = $crawler->rec_id;
      
        // reset if destinations have all been parsed
        if ($crawler->rec_id >= $crawler->rec_tot)
        {
           
          $this->cronupdate($api,0,null,0,'CYCLE Complete',3);
          //$this->emailcroninfo("Alert! webtub {$api} API - CYCLE complete","Search API {$crawler->dest_id} : {$crawler->dest_tot} ");
          
        }
        else
        {
          // get Destinations to parse
          $limit = ($crawler->limit - $crawler->used );
          $destinationSearch = new destinationSearch();
  
          $recommendations = Recommendation::getRecommendationDiff($crawler->rec_id,$offset,$api);
           
          if($recommendations){
        
            $search = new $api();
            foreach($recommendations as $rec){
              if( $limit > 0 ){
        
                // decrement limit & increment used for each call
                logger::log('Cron Run! '.$api);
                $limit = $limit - 1;
                $crawler->used = $crawler->used + 1;
             
                $results = $search->getByTitle($rec,$rec['title']);
                 
                if($results){
                    
                  foreach ($results as $result)
                  {
                     //logger::log('saving the result');
                    if(!empty($result)){
                      $data = '';
                      $data[$search->getSource()] = $result;
                      $recommendations = new recommendation($rec['id'],$rec['cat_id'], $result['title']);
                   
                      $recommendations->saveApiData($data,$rec['rec_id'], $api);
                       $rec_id = $rec['rec_id'];
                    }
                  }
                }else{
                  
                  // No Result
                  $rec_id = $rec['rec_id'];
                  //var_dump($rec_id);
                  //echo "No Result";
                }
              }else{
                logger::log('Cron Limit Reached! '.$api);
                $rec_id = isset($rec_id )?$rec_id:$crawler->rec_id;
               
              }
            }
          }else{
            $rec_id = $crawler->rec_id + $offset;
          }
            
          $this->cronupdate($api,null,$rec_id,$crawler->used,'Cron Run');
          
        }  
 
    }
  }
  
   
  /**
   *   crondb function
   */
  public function crondb($api)
  {
    $db = new dbMgr();
      
    
    $sql  = " SELECT cron.* ";
    $sql .= " , ( SELECT  max(id)  as dest_tot FROM destinations) as dest_tot  ";
    $sql .= " , ( SELECT  IFNULL(max(id),0)  as rec_tot FROM recommendations WHERE source != '{$api}' ) as rec_tot    ";
    $sql .= " FROM cron  ";
     $sql .= " WHERE api = '{$api}' ";
  
    $db->query($sql);
    $result = $db->getRowsAsArray();
     $db->closeStmt();
    return (object)$result[0];  
  }
  

  /**
   * cronupdate
   */
  public function cronupdate($api,$dest_id,$rec_id,$used,$info = null,$freq = null  )
  {
    $db = new dbMgr();
    
    $sql = " UPDATE cron SET used = {$used} , datetime = NOW()  ";
    
    if(isset($dest_id)){
      $sql .= " , dest_id = {$dest_id}";
    }else if(isset($rec_id)){
      $sql .= " , rec_id = {$rec_id}  ";
    }
    if($info){
      $sql .= " , info = '{$info}'";
    }
    if($freq){
      $sql .= " , freq = {$freq}";
    }
    
    $sql .= "  WHERE api = '{$api}' ; ";
    
    $db->query($sql);
    $db->closeStmt();
    
  }
    
  /**
   * resetcron  
   */
   public function resetcron()
  {
    $db = new dbMgr();
    $sql = " UPDATE cron SET used = 0; ";
    $db->query($sql);
    $db->closeStmt();
  
  }
  
  /**
   * resetcron
   */
  public function cronlock($state)
  {
    $db = new dbMgr();
    $sql = " UPDATE cron SET cron.lock = {$state} ; ";
    $db->query($sql);
    $db->closeStmt();
  
  }

  /**
   * changeSearch -- resetcron function
   */
   public function changeSearch($freq)
  {
    $db = new dbMgr();
    $sql = " UPDATE cron SET freq = {$freq}; ";
    $db->query($sql);
    $sql = " UPDATE destinations SET cached = 0; ";
    $db->closeStmt();
  
  }
  
  /**
   * deactivateApi -- deactivate specific API function
   */
   public function deactivateApi($api,$info)
  {
    $db = new dbMgr();
    $sql = " UPDATE cron SET active = 1, info = '{$info}' WHERE api ='{$api}' ; ";
    $db->query($sql);
    $db->closeStmt();
  
  }
  
  /**
   * activateApi --   
   */
  public function activateApi($api = null,$info)
  {
    $db = new dbMgr();
    $sql  = " UPDATE cron SET active = 0, info = '{$info}' ";
    if($api != null){
      $sql .=  " WHERE api ='{$api}' ; ";
    }
    
    $db->query($sql);
    $db->closeStmt();
  
  }
  
  /**
   * cronstatus --   
   */
  public function cronstatus()
  {
    $db = new dbMgr();
    $sql = " SELECT FLOOR(sum(freq)/count(id)) as status
        , FLOOR(sum(cron.lock)/count(id)) as locked 
        FROM cron ; ";
    $db->query($sql);
    $result = $db->getRow();
    $db->closeStmt();
    return $result;
  }
  
  /**
   * getActiveApis
   */
   public function getActiveApis($freq)
  {
    $db = new dbMgr();
    $sql = " SELECT api FROM cron WHERE active = 0 AND freq = {$freq}; ";
    $db->query($sql);
    $result = $db->getRowsInArray();
    $db->closeStmt();
    return $result;
  }
  
  /**
   * emailcroninfo
   */
  public function emailcroninfo($status,$response)
  {
    $db = new dbMgr();
    $sql = " select * from cron; ";
    $db->query($sql);
    
    $message  = '<table cellspacing="0" cellpadding="0" style="font-family:helvetica; font-size:14px;" >';
    $message .= '<tr bgcolor="#3399cc"  ><td width="40" >id</td><td  width="100" >api</td><td width="80" >dest_id</td><td width="80" >rec_id</td>';
    $message .= '<td width="80" >used</td><td width="80" >limit</td><td width="200" >info</td><td width="60" >active</td><td width="160" >datetime</td></tr>';
    
    while($row = $db->getRow()) {  
      $message .= "<tr><td>{$row["id"]}</td><td>{$row["api"]}</td><td>{$row["dest_id"]}</td><td>{$row["rec_id"]}</td>";
      $message .= "<td>{$row["used"]}</td><td>{$row["limit"]}</td><td>{$row["info"]}</td><td>{$row["active"]}</td><td>{$row["datetime"]}</td></tr>";
      
    }
    $message .= "</table>";
    $message .= "<pre>";
    $message .= json_encode($response,true);
    $message .= "</pre>";
    $db->closeStmt();
    $this->sendmail($message,$status); 
    
  }
  
  /**
   * sendmail
   */
  public function sendmail($message,$status)
  {
    // multiple recipients
    $to  = 'martin.mccarthy@britny.se' ; // note the comma
      
    // subject
    $subject = $status ;
     
    // To send HTML mail, the Content-type header must be set
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    
    // Additional headers
    $headers .= 'To: Martin <martin.mccarthy@britny.se> ' . "\r\n";
    $headers .= 'From: Info webtub <info@britny.se>' . "\r\n";
    //$headers .= 'Cc: birthdayarchive@example.com' . "\r\n";
    //$headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";
    
    // Mail it
    try {
      mail($to, $subject, $message, $headers);
    } catch (Exception $e) {
      var_dump($e);
    }  
  }
  
  /**
   * updateCache - updating the query cache when a whole cronjob is done.
   */
  public function updateCache()
  {  
    //$sql = 'SELECT id FROM destinations WHERE cached = 0 ORDER BY id LIMIT 1';
    $sql = 'SELECT id FROM destinations ORDER BY id ';
    
    $db = new dbMgr();
    $db->query($sql);
  
    while( $row = $db->getRow()){
      
      $recSearch = new recommendationSearch();
      $memCache = new memCache();
      $fileCache = new fileCache();
      
       for($i=3;$i<=7;$i++)
      {
        $result = $recSearch->getRecommendationList($row['id'], null, null, 0, $i*5, true);
        
        $hashKey = 'recommendationList'.$row['id'] . 0 . $i*5;
        
        logger::log('saving cache as: '.$hashKey, DEBUG);
        
        $hashKey = md5($hashKey);
        
        $memCache->setCache($hashKey, $result);
        $fileCache->setCache($hashKey, $result);
        
      }
    
      //$sql = 'UPDATE destinations SET cached=1 WHERE id='.$row;
      //$db->query($sql);
    }
    
    $this->changeSearch(4);
    
  }
  
  /**
   * clearCache - clearing the query cache on each cron cycle.
   */
  public function clearCache()
  {
    $unix = time() - config::timeToLive;
    $sql = "DELETE FROM webtub.cache WHERE {$unix} > updateTime ";
   
    $db = new dbMgr();
    $db->query($sql);
  
     
  }
  
}