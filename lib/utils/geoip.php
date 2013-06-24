<?php

/**
  * find the Geo location of the user based on GeoIP data
 */

class geoIP
{
  private $record, $city, $continent, $country, $countryName, $region, $ip; 
  
  public function __construct($ip = null)
  {
    if(isset($ip) && $ip != null)
    {
      $this->ip = $ip;
    }
    else 
    {
      $this->ip = $_SERVER['REMOTE_ADDR'];
    }
    $this->getGeoIPData($ip);
  }
  
  /**
   * getGeoIPData - get the data and put it into variables
   */
  private function getGeoIPData()
  {
    if(function_exists('geoip_record_by_name') && $this->ip != '127.0.0.1' && $this->ip != null)
    {
      $this->record = geoip_record_by_name($this->ip);
    
      $this->city = $this->record['city'];
      $this->continent = $this->record['continent_code'];
      $this->country = $this->record['country_code'];
      $this->countryName = $this->record['country_name'];
      $this->region = $this->record['region'];
    }
  }
  
  
  /**
   * @param string $name the name of the record property you want.
   * @throws exception if property not found
   * @return string 
   */
  public function __get($name)
  {
    if(in_array(strtolower($name), $this->record))
    {
      return $this->record[strtolower($name)];
    }
     else
    {
      throw new exception('Property not found!');
    }
  }
  
  public function getCity()
  {
    return $this->city;
  }
  
  public function getCountry()
  {
    return $this->country;
  }
  
  public function getCountryName()
  {
    return $this->countryName;
  }
  
  public function getContinent()
  {
    return $this->continent;
  }
  
  public function getRegion()
  {
    return $this->region;
  }
}