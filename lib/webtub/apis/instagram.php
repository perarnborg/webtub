<?php

class instagram implements iApi {
  
  private $source = 'instagram';
  private $images = array();
  
  public function search($lat, $lng) 
  {
  
    $this->results = array();
    $url = 'https://api.instagram.com/v1/media/search?lat=' . $lat . '&lng=' . $lng . '&&client_id=58fccc8b8bb54ee1a7dfc5b7b53bd936';
    
    $request = new httpRequest($url);
    $errorMessage = NULL;
    $response = $request->getResponse($errorMessage);
    
    if($response)
    {
      
      $response = json_decode($response);
      if(isset($response->data)) {
        foreach($response->data as $image) {
          $webtubImage = new webtubImage();
          $webtubImage->source = $this->source;
          $webtubImage->link = $image->link;
          if($image->caption) {
            $webtubImage->caption = $image->caption->text;
          }
          $webtubImage->url = $image->images->standard_resolution->url;
          $webtubImage->urlThumb = $image->images->thumbnail->url;
          array_push($this->images, $webtubImage);
        }
      }
        
      //var_dump($title);
    }else{
      
    } 
    
    return $this->images;
  
    
  }
}
?>