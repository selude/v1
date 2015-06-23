<?php
class Image
{
 public static function _getAlt($path)
 {
  $path = str_replace(array(':',"'"),'',$path);
  return trim(preg_replace('/(-|_|\/)/','-',$path),'-');
 }
}