<?php
class session
{
 public static function get($var,$def=false)
 {
  if(!isset($_SESSION))
  {
   session_start();
  }
  $var = explode('[',trim($var,']'));
  // @session_destroy();
  $res = defined('BASE_DIR') && isset($_SESSION[BASE_DIR])?$_SESSION[BASE_DIR]:false;
  if(!$res)
   return false;
  for($i=0; $i<count($var) && $res; $i++)
  {
   // echo '<br>checking :'.$res[$var[$i]];
   $res = isset($res[$var[$i]]) ? $res[$var[$i]] : $def;
  }
  // $res = isset($_SESSION[$var[0]]$$temp)?$_SESSION[$var]:$def;
  return $res;
 }
 
 public static function set($var,$val=false)
 {
  if(!isset($_SESSION))
  {
   session_start();
  }
  defined('BASE_DIR') || define('BASE_DIR',basename(dirname(dirname(__FILE__))));
  $_SESSION[BASE_DIR][$var]=$val;
 }
 
 public static function getUser()
 {
  return self::get('user');
 }
}

class history
{
 public static function push($url = 'this')
 {
  $url = str_replace('this',$_SERVER['REQUEST_URI'],$url);
  $temp = session::get('history');
  $history = $temp? $temp : array();
  if(@$history[count($history)-1]==$url)
   return;
  $history[] = $url;
  session::set('history',$history);
 }
 
 public static function pop()
 {
  $temp = session::get('history');
  $history = $temp? $temp : array();
  $url = count($history)? array_pop($history): false;
  session::set('history',$history);
  return $url;
 }
}