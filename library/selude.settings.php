<?php
include_once 'library/DBA.php';
include_once 'library/router.php';
include_once 'library/entity.php';
include_once 'library/session.php';
include_once 'library/pages.php';
include_once 'library/PHProp-master/PHProp.php';
global $CURRENT_PAGE;
global $CURRENT_VIEW;
global $view_filetypes;
global $configuration;
global $plugins;
global $URI;
global $URI_chunks;

class Configuration
{ 
 public static function setupVariables()
 {
  global $view_filetypes;
  global $configuration;
  global $plugins;
  global $live_site;
  $config = session::get('config');
  // if(!$config)
  {
   $config = PHProp::parse("config/application.ini",'.');
   session::set('config',$config);
  }
  $configuration = $config;
  // print_r($configuration);
  // die;
  
  $about = $config['about'];
  $paths = $config['paths'];
  $credentials = $config['credentials'];
  $plugins = $config['plugins'];
  $def = $config['default'];
  $defines = $config['defines'];
  
  $temp = $config['site_settings']->include->app_name;
  if($temp=='true'||$temp=='1')
  {
   defined("SITE_NAME") || define("SITE_NAME",$about->app->name);
  }
  $temp = isset($config['site_settings']->cache)?$config['site_settings']->cache:false;
  if($temp)
  {
   foreach($temp as $key=>$val)
   {
    if($val==1 || $val==true || $val=='true')
    {
     defined('CACHE_'.strtoupper($key)) || define('CACHE_'.strtoupper($key),1);
    }
   }
  }
  
  defined('DS') || define("DS", "/");
  $APP_NAME = basename(dirname(dirname(str_replace('\\','/',__FILE__))));
  defined('BASE_PATH') || define('BASE_PATH',dirname(dirname(str_replace('\\','/',__FILE__))));
  // echo BASE_PATH;die;
  defined("APP_NAME") || define("APP_NAME",$APP_NAME);
  $host = strtolower($_SERVER['HTTP_HOST']);
  $protocol = explode('/',$_SERVER['SERVER_PROTOCOL']);//gives HTTP/1.0
  $protocol = strtolower($protocol[0]);//gives HTTP
  $base = $protocol.'://'.$host;
  if(in_array($host,array('localhost','127.0.0.1')))
  {
   defined ('local') || define('local',true);
   defined("APPLICATION_PATH") || define("APPLICATION_PATH",$base.DS.APP_NAME);
  }
  else
   //definitions from Server
   defined("APPLICATION_PATH") || define("APPLICATION_PATH",$base);
   
  defined('baseURL') || define('baseURL',APPLICATION_PATH);  
  defined('META') || define('META',baseURL.DS.'meta'.DS);
   
  defined("LIB_PATH") || define("LIB_PATH",'library'.DS);
  defined("PLUGIN_PATH") || define("PLUGIN_PATH",LIB_PATH.'plugins'.DS);
  defined("DB_PATH") || define("DB_PATH",'library'.DS.'DBA.php');
  defined("SESSION_PATH") || define("SESSION_PATH",'library'.DS.'session.php');
  defined("JS_BASEVAR")||define("JS_BASEVAR",$paths['jsBaseVar']);
  defined("PLUGIN")||define("PLUGIN",'plugins/');
  
  //-----------Define const for Database using vars from config data
  defined('DB_HOST') || define('DB_HOST',$credentials->db->host);
  defined('DB_NAME') || define('DB_NAME',$credentials->db->name);
  defined('DB_USER') || define('DB_USER',$credentials->db->username);
  defined('DB_PWD') || define('DB_PWD',$credentials->db->password);
  
  //-----------Define const for PLUGINS using index names from config data
  if(count($plugins))
  foreach($plugins as $name=>$path)
  {
   $name = strtoupper($name);
   if(trim($path->basedir)!='' && !defined($name))
    define($name,LIB_PATH.'Plugins'.DS.$path->basedir);
  }
    
  //-----------User defined constants from config.ini, eg., path for images
  if(count($defines))
  foreach($defines as $name=>$path)
  {
   if($name && $path && !defined($name))
   {
    $ds = preg_match('/app_path/i',$path)? DS:'';
    $path = str_ireplace('app_path',baseURL,trim($path,DS)).$ds;
    define($name,$path);
   }
  }
  // die;
  if(isset($about->app))
  {
   foreach($about->app as $key=>$val)
    defined(strtoupper('APP_'.$key)) || define(strtoupper('APP_'.$key),$val);
  }
  defined('SERVER_NAME') || define('SERVER_NAME',$_SERVER['SERVER_NAME']);
  $error_reporting = empty($config['site_settings']->error_reporting) ?false :$config['site_settings']->error_reporting;
  $live_site = !preg_match('/(localhost|127.0.0.1)/',SERVER_NAME);

  
  // PHP_OS == "Windows" || PHP_OS == "WINNT" ? defined('DS') || define("DS", "/") : defined('DS') || define("DS", "/");
  
  $view_filetypes = explode(',',$def['view_filetypes']);
 }
 
 public static function setPathsJS($js=false)
 {
  $config = session::get('config');
  if(!$config)
   return;
  $paths = $config['js_paths'];
  // print_r($paths);
  // die;
  $temp = ob_get_contents();
  @ob_end_clean();
  $str = '<script>';
  foreach($paths as $key=>$path)
  {
   if($key && $path)
   {
    $ds = preg_match('/app_path/i',$path)? DS:'';
    $path = str_ireplace('app_path',baseURL,trim($path,DS)).$ds;
    $str .= "$key = '$path';"."\n";
   }
  }
  $str .= '</script>';
  $temp = str_ireplace('</head>',$str.'</head>',$temp);
  ob_start();
  echo $temp;
 }
 
 function setResourcePath($base)
 {
  // echo "defining path from base $base";die;
  defined("thisJS") || define("thisJS",$base."js/");
  defined("thisCSS") || define("thisCSS",$base."css/");
  defined("thisIMAGE") || define("thisIMAGE",$base."images/");
 } 
}

class URL extends sRequest
{
 var $chunks;
 var $data_chunks;
 var $page;
 var $view;
 var $config;
 
 function __construct()
 {
  $post = count($_POST) ?true:false;
  $get = count($_GET)?true:false;
  define('post',$post);
  define('POST',$post);
  define('get',$get);
  define('GET',$get);
  
  self::getURLChunks();
 }
  
 function getURLChunks($configure=true)
 {
  global $URI,$URI_chunks;
  $URI = $_SERVER['REQUEST_URI'];
  
  // echo '<br>actual URL: '.$URI;
  $route = sRoute::decodeRoute($URI);
  // echo '<br>Returned URL: '.$URI;
  // die;
  $URI = $route? $route : $URI;
  // echo '<br>'.print_r($URI,true);die;
  // if($route && defined('local'))
   // $URI = trim(str_replace(APP_NAME,'',$URI),DS);
  // echo '<br>trimmed URL: '.$URI;
  
  $this->chunks = explode('?',$URI);//separate data and uri part
  
  $temp = isset($this->chunks[1])?$this->chunks[1]:false;
  
  $this->chunks = isset($this->chunks[0])?$this->chunks[0]:false;
  defined('DS') || define("DS", "/");
  $this->chunks = explode(DS,$this->chunks);
  $URI_chunks = $this->chunks;
  // print_r($this->chunks);die;
  if($configure)
  {
   global $config;
   $config = new Configuration;
  }
  return $this->chunks;
 }
 
 function getPage()
 {
  global $CURRENT_PAGE;
  global $CURRENT_VIEW;
  global $URI;
  $this->chunks = explode(DS,$URI);
  $index = 0;
  $ch = $this->chunks;
  $this->page = !count($ch) || $ch[$index]==''? 'default': $ch[$index];
  $page = $this->page;
  // echo '<br>page: '.$page; 
  // die;
  if($this->page!="default")//check if this is a valid page in scope
  {
   $list = scandir("pages");
   // print_r($list);die;
   if(!in_array($this->page,$list))
   {
     $this->page = "Error404";
   }
  }
  // $this->config->setLayoutPath($this->page);
  $this->view = !isset($ch[$index+1])||$ch[$index+1]==''?"index":$ch[$index+1];
  // echo '<br>view: '.$this->view;die;
  $CURRENT_PAGE = $this->page;
  $CURRENT_VIEW = $this->view;
  $this->getURLData();
  defined("CURRENT_PAGE") || define("CURRENT_PAGE",$this->page);
  defined("page") || define("page",$this->page);
  defined("PAGE") || define("PAGE",$this->page);
  defined("CURRENT_VIEW") || define("CURRENT_VIEW",$this->view);
  defined("view") || define("view",$this->view);
  defined("VIEW") || define("VIEW",$this->view);
 }
 
 function DBO()//DataBaseObject
 {
  require_once 'entity.php';
  $temp = new Entity;
  return $temp;
 }
 
 function _getDCO($page=CURRENT_PAGE)//DataControllerObject
 {
  $base = "pages/$page/controllers/modal.php";
  // $path = $page!=CURRENT_PAGE?str_replace(CURRENT_PAGE,$page,$base):$base;
  // print_r($base);die;
    // var_dump(debug_backtrace());
  if(!file_exists($base))
   return false;
  require_once $base;
  // echo $path;die;
  $class_name = $page.'Modal';
  $temp = new $class_name;
  return $temp;
 }
 
 function redirect($to,$abs = false)
 {
  ob_end_clean();
  $temp = explode('?',$to);
  $to = str_ireplace('this.',CURRENT_PAGE.'.',$temp[0]);
  $data = isset($temp[1])?$temp[1]:false;
  // echo $to;die;
  $process = count(explode('.',$to))>1?true:false;
  if($process)
  {
   $temp = str_replace('.',DS,$to);
   $to = baseURL.DS.$temp;
  }
  else if($abs)
   $to = baseURL.DS.str_replace('.',DS,$to);
  if($data)
   $to .= '?'.$data;
  // echo $to;die;
  header('Location:'.$to);
 }

 public static function _next($val)
 {
  global $URI_chunks;
  if(!count($URI_chunks))
   return false;
  foreach($URI_chunks as $item)
  {
   if($item==$val)
   {
    return next($URI_chunks);
   }
  }
  return false;
 }
}

class sRequest
{
 static $GET;//to set $_GET variables
 static $POST;//to set $_POST variables
 static $REQUEST;//to set $_REQUEST variables
 
 public static function getURLData()//sets 
 {
  global $CURRENT_PAGE;
  global $CURRENT_VIEW;
  global $URI;
  $chunks=explode('?',$URI);//separate data and uri part
  if(!isset($chunks[1]))//data not found
  {
   $chunks = explode(DS,$chunks[0]);
   $base = array_search($CURRENT_PAGE,$chunks);
   $base = $base+2;//skip view too
   $temp = Array();
   for($i=$base; $i<count($chunks)-1; $i+=2)
    $temp[$chunks[$i]] = $chunks[$i+1];
   self::$GET = $temp;
  }
  else
  {
   $chunks = $chunks[1];
  
   self::$GET = array();
   $_GET = &self::$GET;
   $temp = Array();
   if(!$chunks)
    return false;
   $data = explode('&',$chunks);
   foreach($data as $val)
   {
    $val = explode('=',$val);
    $temp[$val[0]] = $val[1];
   }
   self::$GET = $temp;
  }
  $_GET = &self::$GET;
  // print_r(self::$GET);die;
 }
 
 public static function getVar($var,$def=false,$which='get')
 {
  $temp = strtolower($which)=='post'? $_POST : $_GET;
  $temp = isset($temp[$var])? $temp[$var]: $def;
  return trim(urldecode($temp));
 }
 
 public static function _getVar($var,$def=false,$which='get')
 {
  $temp = $which? '$_'.strtoupper($which): $_REQUEST;
  $temp = isset($temp[$var])? $temp[$var]: $def;
  return trim(urldecode($temp));
 }
 
 public static function _post($var,$def=false)
 {
  $temp = isset($_POST[$var])? $_POST[$var]: $def;
  $temp = is_array($temp)?$temp:trim(filter_var($temp));
  return $temp;
 }
 
 public static function _get($var,$def=false)
 {
  $temp = isset($_GET[$var])? $_GET[$var]: $def;
  return urldecode(trim(filter_var($temp)));
 }
 
 public static function _getViewKey($name)
 {
  $url_chunks = explode(DS,$_SERVER['REQUEST_URI']);
  $res = array();
  for($i=0; $i<count($url_chunks); $i++)
  {
   if($url_chunks[$i]==$name)
   {
    $res['key'] = $url_chunks[$i];
    $res['value'] = isset($url_chunks[$i+1])? $url_chunks[$i+1] :null;
   }
  }
  return $res;
 }
 
 public static function getVal($name,$def=false,$which='get')
 {
  $which = strtolower($which);
  if($which=='post')
   $temp = $_POST;
  else if($which=='get')
   $temp = $_GET;
  else
   $temp = $_REQUEST;
  $temp = isset($temp[$name])? $temp[$name]: $def;
  return trim($temp);
 }
 
 public static function filter($var)
 {
  return trim($var);
 }
 
 public static function filterVar($var)
 {
  $temp = $var;
  $temp = strtolower(str_replace(' ','_',trim($temp)));  
  return trim($temp);
 }

 public static function toUrlVar($var)
 {
  $temp = $var;
  $temp = strtolower(str_replace(' ','-',trim($temp)));  
  return trim($temp);
 }
}
