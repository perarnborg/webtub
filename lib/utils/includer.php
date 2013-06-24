<?php
/**
 * 
 * Helper for including files
  *
 */
class includer
{
  /**
   * construct
   * Include all the files in the array
   * @param $includeFiles array() files to include
   */
  public static function includeFiles($includeFiles, $vars = array())
  {
    if(config::loggerSeverity < DEBUG)
    {
      $debugData = $vars;
    }
    if(is_array($vars))
    {
      foreach ($vars as $k=>$v) {
        ${$k} = $v;
      }
    }
    else 
    {
      throw Exception('includeFiles vars needs to be array', 1);
    }

    foreach($includeFiles as $file)
    {
      if(is_file(config::templateDir().'/'.$file))
      {
        include config::templateDir().'/'.$file;
      }
    }
  }
}