<?php
class utils
{
  
  /**
   * array diff for multidimensional arrays
   * @param array $aArray1
   * @param uarray $aArray2
   * @return multitype:array or false 
   */
  static public function arrayRecursiveDiff($aArray1, $aArray2)
  {
    $aReturn = array();
      
    foreach ($aArray1 as $mKey => $mValue) {
      if (array_key_exists($mKey, $aArray2)) {
        if (is_array($mValue)) {
          $aRecursiveDiff = self::arrayRecursiveDiff($mValue, $aArray2[$mKey]);
          if (count($aRecursiveDiff)) {
            $aReturn[$mKey] = $aRecursiveDiff;
          }
        } else {
          if ($mValue != $aArray2[$mKey]) {
            $aReturn[$mKey] = $mValue;
          }
        }
      } else {
        $aReturn[$mKey] = $mValue;
      }
    }
      
    return $aReturn;
  }
}