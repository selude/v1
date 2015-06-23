<?php
include_once 'config/routes.php';
class sRoute
{
 public static function decodeRoute(&$URL,$reverse=false)
 {
  global $routes;
  $URL = trim($URL,DS);
  $URL = $reverse?str_replace(' ','-',str_replace(':','',$URL)):$URL;
  $URL = $reverse?strtolower(trim($URL,'-')):$URL;
  
  // $BASE = session::get('base');
  $BASE = false;
  if(!$BASE)
  {
   $BASE = basename(dirname(dirname(__FILE__)));
   defined('base') || define('base',$BASE);
   session::set('base',$BASE);
  }
  $URL = explode(DS,$URL);
  $arr = array();
  for($i=0; $i<count($URL); $i++)
  {
   $temp = $URL[$i]!=''?$URL[$i]:false;
   if($temp)
    $arr[] = $temp;
  }
  $URL = $arr;
  $len = 0;
  // echo '<br>Base is: '.$BASE;
  $len = array_search($BASE,$URL);
  // echo '<br>len is: '.var_dump($len);
  // echo '<br>count is: '.count($URL);
  // echo '<br>url is: '.print_r($URL,true);die;
  $temp = array();
  if($len===false)
   $temp = $URL;
  else
  for($i=$len+1; $i<count($URL); $i++)
  {
   $temp[] = $URL[$i];
  }
  // echo '<br>temp is: '.print_r($temp,true);die;
  $URL = implode(DS,$temp);
  if(count($routes))
  {
   // echo '<br>URL: '.$URL;die;
   foreach($routes as $key_url => $old_url)
   {
    $match = !$reverse ?$key_url :$old_url;//for reverse, match value; otherwise, key
    $replace = !$reverse ?$old_url :$key_url;//for reverse, replace with key; otherwise, wth value
    // echo '<br>finding <b>'.$match.'</b> in <b>'.$replace.'</b>';
    // echo '<br>URL is <b>'.$URL.'</b>';
        
    $matches = array();
    $skip = false;
    $temp = explode(DS,$URL);
    $pattern = explode(DS,$match);
    if(count($pattern)!=count($temp) && !$reverse)
     continue;//case when not a matching url
     
    for($i=0; $i<count($temp) && !$skip; $i++)
    {
     // echo '<br>Matching <b>'.$temp[$i].'</b> with <b>'.$pattern[$i].'</b>';
     if(isset($pattern[$i]) && ($temp[$i]==$pattern[$i]
        // || preg_match('/\-+/',$temp[$i])
        ))
      continue;
     else if(preg_match('/\$\d/',@$pattern[$i]))
     {
      $matches[$pattern[$i]] = $temp[$i];
     }
     else
      $skip = true;
    }
    if(!$skip)
    {
     $old_url = explode(DS,$replace);
     if(count($matches))
     {
      foreach($matches as $key=>$replacer)
      {
       $index = array_search($key,$old_url);
       if($index===false)
        continue;
       else
        $old_url[$index] = $replacer;
      }
     }
     // $URL = $reverse? $old_url.DS.str_replace(' ','-',$reverse):$old_url;
     $URL = implode(DS,$old_url);
     // if(preg_match('/play/i',$URL))
      // die(print_r($URL,true));
     return $URL;
    }
   }
  }
  // echo 'original: '.$URL;die;
  return $URL;
 }

/* 
 public static function decodeRoute(&$URL,$reverse=false)
 {
  global $routes;
  $URL = trim($URL,DS);
  
  $BASE = session::get('base');
  if(!$BASE)
  {
   $BASE = basename(dirname(dirname(__FILE__)));
   define('base',$BASE);
   session::set('base',$BASE);
  }
  $URL = trim($URL,DS);
  $URL = explode(DS,$URL);
  $len = 0;
  // echo '<br>Base is: '.$BASE;
  $len = array_search($BASE,$URL);
  // echo '<br>len is: '.$len;
  // echo '<br>count is: '.count($URL);
  // echo '<br>url is: '.print_r($URL,true);
  $temp = array();
  if($len===false)
   $temp = $URL;
  else
  for($i=$len+1; $i<count($URL); $i++)
  {
   $temp[] = $URL[$i];
  }
  // echo '<br>temp is: '.print_r($temp,true);
  $URL = implode(DS,$temp);
  if(count($routes))
  {
   // echo '<br>URL: '.$URL;die;
   foreach($routes as $key_url => $old_url)
   {
    $match = !$reverse ?$key_url :$old_url;
    $replace = !$reverse ?$old_url :$key_url;
    // echo '<br>finding <b>'.$match.'</b> in <b>'.$replace.'</b>';
    // echo '<br>URL is <b>'.$URL.'</b>';
        
    $matches = array();
    $skip = false;
    $temp = explode(DS,$URL);
    $pattern = explode(DS,$match);
    if(count($pattern)!=count($temp) && !$reverse)
     continue;//case when not a matching url
     
    for($i=0; $i<count($temp) && !$skip; $i++)
    {
     // echo '<br>Matching <b>'.$temp[$i].'</b> with <b>'.$pattern[$i].'</b>';
     if(isset($pattern[$i]) && ($temp[$i]==$pattern[$i]
        || preg_match('/\-+/',$temp[$i])))
      continue;
     else if(preg_match('/\$\d/',@$pattern[$i]))
     {
      $matches[$pattern[$i]] = $temp[$i];
     }
     else
      $skip = true;
    }
    if(!$skip)
    {
     $old_url = $replace;
     if(count($matches))
     foreach($matches as $key=>$replace)
      $old_url = str_replace($key,$replace,$old_url);
     // $URL = $reverse? $old_url.DS.str_replace(' ','-',$reverse):$old_url;
     $URL = $old_url;
     return $URL;
    }
   }
  }
  // echo 'original: '.$URL;die;
  return $URL;
 } */

 public static function getOptimisedRoute($URL,$optional=true)
 {
  return self::decodeRoute($URL,$optional);
 }

}