

<?php

class flickr implements iApi {
  
  private $source = 'flickr';
  private $results = array();
  private $images = array();
  
  public function search($lat, $lng) 
  {
    return false;
  }
    public function getByTitle($destination, $title) 
  {
  
    $this->results = array();
    $url = 'http://api.flickr.com/services/feeds/photos_public.gne?tags='.str_replace(array(' ', 'City'), array('', ''), $destination['name']).','.str_replace(' ', '', $title);
    
    $request = new httpRequest($url);
    $errorMessage = NULL;
    $response = $request->getResponse($errorMessage);
    
    if($response)
    {
      
      $doc = new DOMDocument();
      $doc->loadXML($response);
   
      $this->events = $this->parseResponse($doc,$title);
       
      //var_dump($title);
    }else{
      
    //  flicker feeds is unlimited and has no error handling response
    } 
    
    return $this->results;
  
    
  }

  /**
   * parses the flickr specific data
   * @param array $response xml decoded directly from yelp
   */
  private function parseResponse($doc,$title)
  {
    $this->images = Array(); 
      foreach ($doc->getElementsByTagName('entry') as $node) {
        
        $test  =  $node->getElementsByTagName('title')->item(0)->nodeValue;
        
        if($title == $test){
          
         
          $image = array('source' => 'flickr');
          $linkNodes = $node->getElementsByTagName('link');
          
          for ($i = 0; $i < $linkNodes->length; $i++) {
            if($linkNodes->item($i)->getAttribute('type') == 'text/html') 
            {
              $image['link_url'] = $linkNodes->item($i)->getAttribute('href');
              
            }
            if($linkNodes->item($i)->getAttribute('type') == 'image/jpeg') 
            {
              $image['url'] = $linkNodes->item($i)->getAttribute('href');
            }
          }
          $author = $node->getElementsByTagName('author');
          if($author->length > 0) {
            foreach ($author as $item) {
              $image['user'] =  $item->getElementsByTagName('name')->item(0)->nodeValue;
            }
          }
          
          $image['title'] = $test;
          $image['score'] = 1;
          
          if(isset($image['url']) && count($this->images) < 6)
          {
            array_push($this->images, $image);
          } 
        //var_dump($image);
        }
      }
  
    foreach($this->images as $image){
      
      
      $result = array();
      $result['title'] = $image['title'];
      $result['source'] = $image['source'];
      $result['link_url'] = $image['link_url'];
      $result['url'] = $image['url'];
      $result['user'] = $image['user'];
      $result['score'] = $image['score'];
      
      $this->results[] = $result;
    
    }
     
    return $this->results;
    
  }
 
   
  
    /**
     * get the name of the api
     */
  public function getSource(){
    return $this->source;
  }
  public function setScore(&$result) 
  {
    $result->score = 0;
  }
}
?>