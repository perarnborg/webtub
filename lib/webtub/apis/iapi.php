<?php
/* foreach( webtub::activeApis() as $api ) 
{
  require_once config::libDir().'/webtub/apis/'.$api.'.php';
} */
interface iApi {
  
  /**
   * Search the api for locations
   * @param /destination $destination
   * @param string or array $keywords
   * @param timestamp $fromDate
   * @param timestamp $untilDate
   */
  public function search($lat, $lng);
}
class webtubImage {
  public $source;
  public $url;
  public $urlThumb;
  public $link;
  public $caption;
}
?>