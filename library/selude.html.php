<?php
include_once 'selude.settings.php';
global $added_files;
global $created_layouts;
global $FILES;
global $DUMP;
class Layout extends URL//URL class is in settings.php
{
 var $view_sections;
 var $url_chunks;
 var $page;
 var $dco;
 var $_dbo;
 var $renderView;
 var $data;
 var $meta;
 
 function __construct($config=false)
 {
  if($config)
  {
   defined('level')||define('level',$this->getPathLevels());
  }
 }
 
 function __destruct()
 {
  global $FILES;
  // session::set('layout_error',1);
  if(!session::get('layout_error'))
  {
   $renderView = $this->renderView;
   $render_view = isset($renderView[VIEW]) ?$renderView[VIEW] :true;
	// echo 'currentView: '.json_encode($renderView).json_encode($render_view);
	// print_r($renderView,true);
  }
  if(!defined('dumpHTML'))
  {
   Configuration::setPathsJS();
   unset($_SESSION['layout_error']);
   $temp = ob_get_contents();
   @ob_end_clean();
   $page = defined('PAGE')?PAGE:'404';
   $temp = str_ireplace('<body>',"<body><div class='page page_".PAGE."'>",$temp);
   $temp = str_ireplace("</body></html>","",$temp);
   if($render_view && !session::get('layout_error'))
   {
    $temp .= "</body></html>";
   }
   $temp = str_ireplace('</body>',"</div></body>",$temp);
   ob_start();
   echo $temp;			
   if($this->meta)
    $this->setMetaTags($this->meta);
   define('dumpHTML',true);
  }
 }
  
 function setLayout($for_page = CURRENT_PAGE)
 {
  global $plugins;
  $this->dco = $this->_getDCO();//in class URL
  // print_r($this->dco);die;
  $this->_dbo = new Entity;
  if($for_page==CURRENT_PAGE)//buffer already started?
   ob_start();
  if($for_page == CURRENT_PAGE)
  {
   $level = $this->getPathLevels();
   echo '
	<!DOCTYPE html>
	<html lang="en">
	<head>
	
	<title>Default title</title>
	<link rel="icon" href="'.$level.APP_ICON.'" type="'.APP_ICON_TYPE.'">
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">';
   if(count($plugins))
   {
	foreach($plugins as $plugin_key=>$details)
	{
	 $base = PLUGIN_PATH.trim($details->basedir,'/');
     global $configuration;
     
     $version = $configuration->about->version;
	 foreach($details->paths as $pathto=>$dir)
	 {
	  $temp = $base.DS.$pathto.DS;
	  if(is_dir($temp))
	  {
	   $files = scandir($temp);
	   for($i=2; $i<count($files); $i++)
	   {
		$type = @end(explode('.',$files[$i]));
		if($type=='css')
        {
            $file_v = !defined('CACHE_CSS')?'?v='.rand(0,100):'?v='.$version;
            echo '<link rel="stylesheet" href="'.$level.$temp.$files[$i].$file_v.'">';
        }
		else if($type=='js')
        {
            $file_v = !defined('CACHE_JS')?'?v='.rand(0,100):'?v='.$version;
            echo '<script type="text/javascript" src="'.$level.$temp.$files[$i].$file_v.'"></script>';            
        }
	   }
	  }
	 }
	}
   }
   $this->_import('library.modal.meta');
   $this->meta = Meta::getMeta($for_page,CURRENT_VIEW);
   echo '
		</head>
	<body>';
  }
  // $path = "layouts/$for_page.php";
  $path = "pages/$for_page/controllers/layout.php";
  // die($path);
  if(!file_exists($path))
  {
   $this->error("missing_layout",$for_page,CURRENT_VIEW,'setLayout',__LINE__);
  }
  include_once $path;//pages/pagename/controllers/layout.php
  $layout_classname = $for_page."Layout";//layout class for page, eg, defaultLayout
  $layout_obj = new $layout_classname;//instance of the above class
  //now, we use data in $layout_obj to assign to current object
  $this->view_sections = $layout_obj->layout_chunks;//get the layout sections set in layoutClass
  if($for_page == CURRENT_PAGE)
    echo "</body></html>";
 }
 
 function setMetaTags($meta)
 {
  $html = '';
  foreach($meta as $key=>$desc)
  {
   $html .= "<meta name='$key' content='".trim($desc,'"')."'>";
  }
  $temp = ob_get_contents();
  @ob_end_clean();
  $temp = str_ireplace("</title>","</title>$html",$temp);
  ob_start();
  echo $temp;
 }
 
 function includeLayout($view_name=VIEW,$page=PAGE, $container_arr=array())
 {
  if($page!=PAGE)
  {
   $page = trim($page);
   require_once "pages/$page/controllers/layout.php";
   $classname = $page.'Layout';
   $tempClass = new $classname;
   $temp_sections = $tempClass->layout_chunks;
  }
  else
   $temp_sections= $this->view_sections;
   
  $view_sections = self::getViewSection($view_name, $temp_sections, $container_arr);
  if(!count($view_sections))//error: no view element found
  {
   // echo 'error:';print_r($view_sections);die;
   $this->error('missing_layout',CURRENT_PAGE,$view_name,'includeLayout',__LINE__);
  }
  $parent_class = false;
  if(isset($view_sections['class']) || isset($view_sections['resources']))
  {
   $class = isset($view_sections['class']) && trim($view_sections['class'])!='' 
            ?trim($view_sections['class']) :false;
   $attr = array();
   $resources = false;
   // if($view_name==CURRENT_VIEW)
    // print_r($view_sections);
   foreach($view_sections as $key=>$val)//add tag attributes
   {
    $key = strtolower($key);
    if($key=='resources')
	 $resources = $val;
    else if($key!='class' && $key!='views' && trim($val)!='')
     $attr[$key] = $val;
   }
   if($resources)//resources to add
   {
    // print_r($res);die;
    if(!is_array($resources))
     $this->_addScript($value);
    else
    {
     foreach($resources as $value)
	  $this->_addScript($value);
    }
   }
   $cur_div = str_replace('-','_',str_replace(' ','',$page.$view_name.$class));
   if(!defined($cur_div) && ( $class || count($attr)))//add
   {
    define($cur_div,true);
    $parent_class = true;
    $str = '<div class="'.$class.'" ';
    foreach($attr as $index=>$attr_val)
     $str .= "$index='$attr_val' ";
    $str = trim($str).'>';
    echo $str;
   }
  }
  $select_view = isset($view_sections['views'])?$view_sections['views']
                 :(!is_array($view_sections)? $view_sections :false);

  if($select_view && is_array($select_view))//view section of returned array
  {
   $container_arr[] = $view_name;//this is necessary, to reach inner levels
   foreach($select_view as $subview_name=>$arr)
   {
	$this->includeLayout($subview_name, $page, $container_arr);
   }
   unset($container_arr);
  }
  else
  {
   if(!$select_view)
   {
    global $DUMP;
    $DUMP = print_r($view_sections,true);
    $DUMP .= print_r($select_view,true);
	$this->error('wrong_definition', $page, $view_name);
   }
   else
   {
    global $view_filetypes;
    $select_view = str_ireplace('this.',"$page.",$select_view);
	$select_view = explode('.',$select_view);
	if(in_array(end($select_view),$view_filetypes))//its a view file
	{
     $this->showView($view_name,$page,join('.',$select_view));
	}
	else
	{
	 $page = $select_view[0];
	 $view = $select_view[count($select_view)-1];
	 $temp_arr = array();
	 for($i=1; $i<count($select_view)-1; $i++)
	  $temp_arr[] = $select_view[$i];
	 $this->includeLayout($view, $page, $temp_arr);
	}
   }
  }  
  if($parent_class)
    echo "</div>";
  return;
 }
  
 function showView($view_name_sent, $cur_page, $view_content)
 {
  global $view_filetypes;
  $view_content = str_ireplace('this.',$cur_page.'.',$view_content);
  $temp = explode('.',$view_content);
  
  // echo '<br>ViewContent: ';print_r($view_content).'<br>';die;
  $temp_name = count($temp)>1? end($temp): $temp[0];
  
  $layout_page = $temp[0];//first element
  // echo '<br>tempname: '.$layout_page;
  // die;
  if(in_array(end($temp),$view_filetypes))//its a file
  {
   // $cur_page = $layout_page;
   $file = $temp[count($temp)-2].'.'.end($temp);
   // echo 'file: '.$file.' CurPage: '.$cur_page;
   $file = "pages/$cur_page/views/$file";
   
    // echo '<br>file: '.$file;
	// die;
   if(!file_exists($file))
    $this->error('missing_resource',$cur_page,$view_name_sent,'showView',__LINE__,$file);
   include_once $file;
  }
  else//its a partial view or view
  {
   $cur_page = $layout_page;
   // echo '<br>Cur_page: '.$cur_page.' ViewContent: '.$view_content.' ViewNameSent: '.$view_name_sent;
   $filepath = 'pages/'.$cur_page.'/controllers/view.php';
   if(!file_exists($filepath))//its a partial view
   {
    $view_content = $cur_page;
	$temp = explode('.',$view_content);
	unset($temp[count($temp)-1]);
	$this->includeLayout($view_content, $cur_page, $temp);
    // return;
   }
   else
   {
    $filepath = 'pages/'.$cur_page.'/controllers/view.php';
    include_once $filepath;//has the viewDefinitions
    $view_class = $cur_page."Page";//create instance of pageClass, eg, homePage
    $view_method = $view_name_sent.'View';//get view method of the class for this view
    $view_obj = new $view_class(false);
 
    $file_path = 'pages'.DS.$cur_page.DS.'views'.DS.$view_content;
    if(method_exists($view_obj,$view_method))//not a partial view
    {
     $view_obj->setLayout($cur_page);//call the view constructor
     $view_obj->$view_method();//call the view class
    }
    else if(file_exists($filepath))//partial view
	{
     include_once $filepath;
	}
    else
     $this->error("missing_resource",$for_page,$view_name_sent,'includeLayout',__LINE__);
   }
  }
  return;
 }
  
 function showPartialView($for)//in form pagename.viewname.subviewname
 {
  $for = explode('.',$for);
  $page_name = $for[0]=='this'?CURRENT_PAGE:$for[0];
  $cont_arr = array();
  for($i=1; $i<count($for); $i++)
  {
   $cont_arr[] = $for[$i];
  }
  $view = $for[$i-1];
  $this->includeLayout($view, $page_name, $cont_arr);
 }
 
 function _addView($for)
 {
  $this->showPartialView($for);
 }
 
 function getViewSection($view,$view_sections,$container_view = array())
 {
  if(count($container_view))
  {
   $inherited_view = false;
   $orig_view_section = $view_sections;
   $temp = true;
   for($i=0; $i<count($container_view) && $temp; $i++)//traverse through to get to direct view
   {
    $temp = isset($view_sections[$container_view[$i]])?$view_sections[$container_view[$i]]:false;
	$view_sections = $temp;
   }
   if(!$temp)//no direct view found
   {
    $inherited_view = true;
    $view_sections = $orig_view_section;
    for($i=1; $i<count($container_view); $i++)//traverse through to get to direct view
    {
     $prev_index = $container_view[$i-1];
     $key = $container_view[$i];
     
     $temp = $view_sections[$prev_index]['views'][$key];
	 $view_sections = $temp;
    }
   }
   if(!$inherited_view && is_array($view_sections['views']) && array_key_exists($view,$view_sections['views']))
    $temp = $view_sections['views'][$view];
  }
  else
  {
   $temp = isset($view_sections[$view])?$view_sections[$view]:(isset($view_sections['all'])?$view_sections['all']:false);
  }
  if(!is_array($temp))//if view is not an array
  {
   $temp2 = $temp;
   $temp = array();
   $temp[$view] = $temp2;
  }
  if(isset($view_sections[$view]) && isset($view_sections['all']))//if layout for this view is defined, pick the common layouts too, defined for all
  {
   foreach($view_sections['all'] as $key=>$view_section)
   {
    if(isset($view_sections[$view][$key]))
     continue;
    $temp[$key]=$view_section;
   }
  }
  if(!$temp)
   return false;
  else
   return $temp;
 }

 function setTitle($new_title="Sample Title")
 {
  $temp = ob_get_contents();
  ob_end_clean();
  if(defined('SITE_NAME'))
   $new_title = trim($new_title).' | '.SITE_NAME;
  $temp = preg_replace('~<title>([^<]*)</title>~i', "<title>$new_title</title>",$temp);
  ob_start();
  echo $temp;
 }
 
 public static function addToHeader($files_arr)
 {
  $temp = ob_get_contents();
  @ob_end_clean();
  global $added_files, $configuration;
  $version = $configuration['about']->version;
  $added_resource = &$added_files;
  for($i=0; $i<count($files_arr); $i++)
  {
   $str = '';
   $file_item = $files_arr[$i];
   // print_r($file_item);
   $filetype = isset($file_item['file_type'])? $file_item['file_type']:
                array_pop(@explode('.',$file_item['file_path']));
   switch($filetype)
   {
     case 'js':
       $temp_path = $file_item['file_path'];
       $temp_path .= !defined('CACHE_JS')?'?v='.rand(0,100):'?v='.$version;
       $str .= "<script type='text/javascript' src='$temp_path'></script>"."\r\n";
     break;
     case 'css':
       $temp_path = $file_item['file_path'];
       $temp_path .= !defined('CACHE_CSS')?'?v='.rand(0,100):'?v='.$version;
       $str .= "<link rel='stylesheet' href='$temp_path'>"."\r\n";
     break;
   }
   $path = str_replace(DS,'-',trim(trim($file_item['file_path'],'.'),'/'));
   // $page = $path[1];
   // print_r($added_resource);die;
   // $file = $path[2];
   if(!is_array($added_resource) || !in_array($path,$added_resource))
    $added_resource[] = $path;
  }
  if(preg_match('/<head>/',$temp))
  {
   if(preg_match('/jquery/',$str))
    $temp = preg_replace("~</title>(.*?)</head>~is","</title>$str$1</head>",$temp);
   else
    $temp = preg_replace("~</title>(.*?)</head>~is","</title>$1$str</head>",$temp);
  }
  else if(preg_match('/<body>/',$temp))
  {
   $temp = str_ireplace("<body>","<body>$str",$temp);
  }
  else if(preg_match('/<div>/',$temp))
  {
   $temp = preg_replace("<div>","<div>$str",$temp,1);
  }
  else
   $temp = $str;
  ob_start();
  echo $temp;
  return $temp;
 }
 
 public static function _addInlineScript($script)
 {
  $temp = ob_get_contents();
  @ob_end_clean();
  if(preg_match('/<head>/',$temp))
   $temp = preg_replace("~</title>(.*?)</head>~is","</title>$1$script</head>",$temp);
  else if(preg_match('/<body>/',$temp))
   $temp = str_ireplace("<body>","<body>$script",$temp);
  else if(preg_match('/<div>/',$temp))
   $temp = preg_replace("<div>","<div>$script",$temp,1);
  else
   $temp = $script;
  ob_start();
  echo $temp;
 }
 
 function setDefaultHTML()
 {
  @ob_end_clean();
  ob_start();
  $level = $this->getPathLevels();
  echo '
  <!doctype html>
  <html>
  <head>
  <title></title>
  <link rel="shortcut icon" href="'.$level.APP_ICON.'">
  </head>
  <body>';
 }
  
 function includePageResources($for_page=page)
 {
  $this->includeJS($for_page);
  $this->includeCSS($for_page);
 }
 
 function includeCSS($for=page, $abs_path=false)
 {
  $level = $this->getPathLevels();
  $files_arr = array();
  if(!is_array($for))//include all css files for a page
  {
   // echo 'this:'.thisCSS;die;
   $home = "pages/$for/resources/css/";
   $files = @scandir($home);
   // print_r($files);die;
   if(!is_array($files))
    $this->error("missing_resource",$for,CURRENT_VIEW,'includeCSS',__LINE__);
     
   foreach($files as $file)
   {
    $temp = array();
    if(preg_match("/^.*\.css/i",$file))
    {
     $temp['file_type'] = "css";
     $temp['file_path'] = $level.$home.$file;
     $files_arr[] = $temp;
    }
   }
  }
  else//include specific files for a page
  {
   foreach($for as $page_name=>$files_list)
   {
    $filepath = $level.'pages'.DS.$page_name.DS.'resources'.DS.'css'.DS;
    for($i=0; $i<count($files_list); $i++)
    {
     $temp = '';
     $filename = $filepath.$files_list[$i];
     if(!file_exists(trim($filename,'../')))
      // die('missing file: '.$filename);
      $this->error("missing_resource",$page_name,CURRENT_VIEW,'includeCSS',__LINE__);
     else
     {
      $temp['file_type'] = 'css';
      $temp['file_path'] = $filename;
      $files_arr[] = $temp;
     }
    }
   }
  }
  $list = '';
  // print_r($files_arr);die;
  if(count($files_arr)) 
   $list = self::addToHeader($files_arr);
  return $list;
 }
 
 function includeJS($for=PAGE)
 {
  $level = $this->getPathLevels();
  $files_arr = array();
  if(!is_array($for))//include all js files for a page
  {
   $home = "pages/$for/resources/js/";
   // echo $home;die;
   $files = @scandir($home); 
   if(!is_array($files))
    $this->error("missing_resource",$for,VIEW,'includeJS',__LINE__);
     
   foreach($files as $file)
   {
    $temp = array();
    if(preg_match("/^.*\.js/i",$file))
    {
     $temp['file_type'] = "js";
     $temp['file_path'] = $level.$home.$file;
     $files_arr[] = $temp;
    }
   }
  }
  else//include specific files for a page
  {
   foreach($for as $page_name=>$files_list)
   {
    $filepath = $level.'pages'.DS.$page_name.DS.'resources'.DS.'js'.DS;
    for($i=0; $i<count($files_list); $i++)
    {
     $temp = '';
     $filename = $filepath.$files_list[$i];
     if(!file_exists($filename))
      // die('missing file: '.$filename);
      $this->error("missing_resource",$page_name,CURRENT_VIEW,'includeJS',__LINE__);
     else
     {
      $temp['file_type'] = 'js';
      $temp['file_path'] = $filename;
      $files_arr[] = $temp;
     }
    }
   }
  }
  $list = '';
  if(count($files_arr)) 
   $list = self::addToHeader($files_arr);
  return $list;
 }
 
 public static function _addCSS($files,$path='',$DS=false)
 {
  if(is_array($files))
  {
   foreach($files['css'] as $js)
    self::_addCSS($js,$path,$DS);
  }
  else
  {
   $files = str_replace('this',PAGE,$files);
   $temp = $DS?$files:str_replace('.',DS,$files);
   $temp = explode(DS,$temp);
   $ext = array_slice($temp,count($temp)-1,1);
   array_splice($temp,count($temp)-1,1);
   if(!$path)
   {
    $path = 'pages/'.$temp[0].'/resources/css/';
    unset($temp[0]);
   }
   else
    $path = str_replace('.',DS,$path);
   $file = $ext[0]!='css'?$ext[0].'.css':$temp[1].DS.$ext[0];
   $file = trim($path,DS).DS.$file;
   $file = level.$file;
   $temp = array();
   $const = trim(trim(str_replace(DS,'-',$file),'.'),DS);
   if(defined($const))
    return;
   define($const,true);
   $temp[0]['file_type'] = 'css';
   $temp[0]['file_path'] = $file;
   Layout::addToHeader($temp);
  }
 }
 
 public static function _addJS($files,$path='',$DS=false)
 {
  if(is_array($files))
  {
   foreach($files['js'] as $js)
    self::_addJS($js,$path,$DS);
  }
  else
  {
   $files = str_replace('this.',PAGE.'.',$files);
   $temp = $DS ?$files :str_replace('.',DS,$files);
   $temp = explode(DS,$temp);
   $ext = array_slice($temp,count($temp)-1,1);
   array_splice($temp,count($temp)-1,1);
   if(!$path)
   {
    $path = 'pages/'.$temp[0].'/resources/js/';
    unset($temp[0]);
   }
   else
    $path = str_replace('.',DS,$path);
   if($ext=='*')
   {
    echo 'ext: '.$ext;
    $files = @scandir($path);
    // print_r($files);
    for($i=0; $i<count($files); $i++)
     self::addJS($files[$i],$path,$DS);
    return;
   }
   $file = $ext[0]!='js'?$ext[0].'.js':$temp[1].DS.$ext[0];
   $file = trim($path,DS).DS.$file;
   $file = level.$file;
   $temp = array();
   $const = trim(trim(str_replace(DS,'-',$file),'.'),DS);
   if(defined($const))
    return;
   define($const,true);
   $temp[0]['file_type'] = 'js';
   $temp[0]['file_path'] = $file;
   Layout::addToHeader($temp);
  }
 }
 
 function getPathLevels()
 {
  $dir = '';
  $chunks=explode('?',$_SERVER['REQUEST_URI']);//separate data and uri part
  
  $this->url_chunks = isset($chunks[0])?explode(DS,$chunks[0]):false;
  $app = trim(APP_NAME);
  $index = -1;
  for($i=0; $i<count($this->url_chunks) && $index==-1; $i++)
  {
   if($this->url_chunks[$i] == $app)
	$index = $i;
  }
  // echo 'index: '.$index.'len: '.count($this->url_chunks);die;
  for($j = $index; $j<count($this->url_chunks)-2; $j++)
   $dir .= '../';
  return $dir;
 }
 
 function processData($data=array(),$view=CURRENT_VIEW)
 {
  if(!count($data))
   $data = $_POST;
  if(isset($data['controller']))
  {
   $fn = $this->dco;
   if(method_exists($fn,$data['controller']))//call the function defined by user
   {
    $temp = $fn->$data['controller']($data);
    return $temp;
   }
   else
    echo "Please define the dataController's dynamic method <b>$fn</b> first before you use it.";
  }
  else if(method_exists($this->dco,$view))
   return $this->dco->$view($data);
  else
   echo "Please define the dataController's method <b>$view</b> first before you use it.";
 }
 
 function dataController($data=array(),$view=CURRENT_VIEW)
 {
  return $this->processData($data,$view);
 }
 
 function execute($data=array(),$view=VIEW)
 {
  return $this->processData($data,$view);
 }

 function error($case, $page=PAGE, $view=VIEW, $method='',$line = '',$res_name='')//page also serves as sender's methodName
 {
  global $live_site;
  self::setDefaultHTML();
  self::_addScript('default.css.error');
  // $live_site = !$live_site;
  if($live_site)
   self::setTitle('Error 404. Page not found!');
  else
  {
    echo "<div class='framework_error'>";
    echo "<h2>Oops, An error occurred!</h2>";
   
   switch($case)
   {
    case "missing_layout_controller":
     self::setTitle('Layout Error');
     echo "<h3>Controller Error: <i><span style='color:red'>Missing Layout Controller</span></i></h3>";
     echo "<p>
        An error occurred while including the <i>LayoutController</i> file for the page <b>$page</b>. Please make sure the required layout controller file <b>layout.php</b> and view controller file <b>view.php</b> are present in directory <b>pages/$page/controllers/</b>.
        <hr><p>
        <span style='color:red'>Error source:<br><b>FILE: library/boot.php";
     if($method)
      echo " | METHOD: $method()";
     if($line)
      echo " | LINE: $line";
     echo "</b></span>";
     break;

    case "missing_layout":
     self::setTitle('Layout Error');
     echo "<h3>View Error: <i><span style='color:red'>Missing View Definition</span></i></h3>";
     echo "<p>
        An error occurred while accessing the layout view from the view array for view <b>$view</b> supposed to be defined in class <b>".$page."Layout</b> in <b>pages/$page/controllers/layout.php</b>. Please make sure a layout file is present in <b>pages/$page/controllers/</b>.
        <hr><p>
        <span style='color:red'>Error source:<br><b>FILE: library/selude.html.php | CLASS: Layout";
     if($method)
      echo " | METHOD: $method()";
     if($line)
      echo " | LINE: $line";
     echo "</b></span>";
     break;

    case "missing_resource":
     self::setTitle('Resource Error');
     echo "<h3>Resource Error: <i><span style='color:red'>Missing Resource Files</span></i></h3>";
     echo "<p>An error occurred while including the resource files for page <b><span style='color:red'>$page</span></b>. Please make sure you've typed the URL correctly. Otherwise, make sure you have all the resource files in directory <b>pages/$page/resources/</b> for page <b>$page</b>.
        <hr><p>
        <span style='color:red'>Error source:<br><b>FILE: library/selude.html.php | CLASS: Layout";
     if($method)
      echo " | METHOD: $method()";
     if($line)
      echo " | LINE: $line";
     if($res_name)
      echo "<br>Details: </b>No resource named as <b>$res_name</b> was found missing.";
     echo "</b></span>";
     break;

    case "missing_modal":
     self::setTitle('Modal Import Error');
     echo "<h3>Modal Error: <i><span style='color:red'>Missing Modal</span></i></h3>";
     echo "<p>An error occurred while importing the modal <b>".@end(explode('.',$res_name))."</b> on page <b><span style='color:red'>$page</span></b>. Please make sure the included modal file is present in directory <b>library/modals/</b>.
        <hr><p>
        <span style='color:red'>Error source:<br><b>FILE: library/selude.html.php | CLASS: Layout";
     if($method)
      echo " | METHOD: $method()";
     if($line)
      echo " | LINE: $line";
     if($resource_name)
      echo "<br>Details: </b>Module named as <b>$res_name</b> was found missing.";
     echo "</b></span>";
     break;

    case "missing_header_layout":
    case "missing_page_layout":
    case "missing_footer_layout":
     self::setTitle('Layout Error');
     echo "<h3>Layout Error: <i><span style='color:red'>".ucwords(str_replace("_"," ",$case))."</span></i></h3>";
     $keys = explode("_",$case);
     $keys = $keys[1];
     echo "<p>Sorry but we cannot detect any keys as <b>$keys</b> in current layout file. Please make sure you haven't erased or renamed the key.
        <hr><p>
        <span style='color:red'>
        Error source:<br><b>FILE: library/selude.html.php | CLASS: Layout | METHOD: includeView</b>
        </span>";
     break;

    case "undefined_view":
     self::setTitle('View Error');
     echo "<h3>View Error: <i><span style='color:red'>Undefined view<span></i></h3>";
     echo "<p>The view you are trying to access has no defined layout. Please define a static (in file) or dynamic layout for this view.
        <hr><p>
        <span style='color:red'>
        Error source:<br><b>FILE: library/selude.html.php | CLASS: Layout | METHOD: includeView()</b><br>
        Error cause: <b>".view."View()</b> was found missing. Please define it in  <b>class ".page."Page</b> in <b>pages/".page."/controllers/layout.php</b>
        </span>";
     break;

    case "wrong_definition":
     self::setTitle('Definition Error');
     echo "<h3>Definition Error: <i><span style='color:red'>Wrong definition<span></i></h3>";
     echo "<p>There is an error in your view definition for view <b>$view</b> for page <b>$page</b>. The <b><i>view</i></b> key was found missing in view definition for view <b>$view</b> of page <b>$page</b>. Please define views in arrays format only in <b>&#36;vComponent[<i>view_name</i>] = array('views'=><i>reference to another view, or view_component</i>);</b><br> eg., <b>&#36;vComponent['$view'] = array('views'=>'default.top');</b>
        <hr><p>
        <span style='color:red'>
        Error source:<br><b>FILE: pages/$page/controllers/layout.php | CLASS: ".$page."Layout | METHOD: __construct</b><br></span>";
     global $DUMP;
     echo '<br>Layout definition right before error:<br>'.$DUMP;
     break;

    case "error404":
     self::setTitle('Error 404: Page not found');
     echo "<h3>Error 404: <i><span style='color:red'>Page not found<span></i></h3>";
     echo "<p>The page you're trying to view does not exist. Please make sure you have typed the URL correctly.
        <hr><p>
        <span style='color:red'>
        Error source:<br><b>FILE: ".baseURL.DS."library/boot.php | Error: <b>&#36;url->page</b> returned as <b>false</b>| Line: <b>$line</b>";
     break;
   }
  }
  echo !$live_site ?"</div>" :null;
  $live_site ?include_once(BASE_PATH.DS.'error.phtml') :null;
  die;
 } 

 function _addScript($files,$from=false)
 {
  if(is_array($files))
  {
   if(!$from)
   {
    $this->error('missing_resource',page,view,'_addScript',__LINE__,$files);
   }
   foreach($files as $filename)
    self::_addScript("$from.$filename");
   return;
  }
  else
  {
   $pages = Page::getList();
   $temp = explode('.',$files);
   $page = $temp[0];//page name
   $page = $page=='this'?page:$page;
   $dir = '';
   if(isset($temp[1]))
   {
    $dir =  $temp[1];//directory name
    unset($temp[1]);
   }
   unset($temp[0]);
   $filename = isset($temp[2])?implode('.',$temp):'';//directory name
   $valid_page = in_array($page,$pages);
   if(!$valid_page)
   {
    $this->error('missing_resource',page,view,'_addScript',__LINE__,$files);
   }
   else if($dir && !in_array($dir,array('js','css','images')) && $dir!='*')
   {
    $this->error('missing_resource',page,view,'_addScript',__LINE__,$files);
   }
   if($valid_page && $dir=='*')//default.*
   {
    // echo 'page:'.$page;die;
    $list = $this->includePageResources($page);//all files
    return $list;
   }
   else if($valid_page && $filename=='*')//all files of specific type
   {
    $includeResources = 'include'.strtoupper($dir);//includeCSS, or includeJS
    $list = $this->$includeResources($page);
    return $list;
   }
   else
   { 
    $level = $this->getPathLevels();
    $file_detail[0]['file_type'] = $dir;
    $file_detail[0]['file_path'] = $level."pages/$page/resources/$dir/$filename.$dir";//$dir is also the file extension
	self::addToHeader($file_detail);
	return $file_detail;
   }
  }
 }

 function _import($scripts,$from=false)
 {
  //the format could be, (array('1.php','2.php','3.php'),'default') or (array('default.1.php','default.2.php','default.3.php'))
  if(is_array($scripts))
  {
   foreach($scripts as $script)
   {
    $path = $from? "$from.$script" : $script;
    self::_import($path);
   }
   return;
  }
  else
  {
   $pages = Page::getList();
   $temp = explode('.',$scripts);
   $page = $temp[0]== 'this'? CURRENT_PAGE :$temp[0];//page name
   unset($temp[0]);
   $dir = isset($temp[1])? $temp[1] :false;
   $filename = '';
   $filename .= implode('.',$temp);//directory name
   $valids  = array('library','plugins','templates');
   $valid_page = in_array($page,$valids)? true :in_array($page,$pages);
   if(!$valid_page)
   {
    // echo 'sdfff';die;
    self::error('missing_resource',CURRENT_PAGE,CURRENT_VIEW,'_import',__LINE__,$scripts);
   }
   else if($valid_page && $dir=='*' && $page!='library')//default.*
   {
    $temp = array_splice($temp,count($temp)-1,1);//remove last item, which may be *
    $temp = implode(DS,$temp);
    $temp = str_ireplace($page,'',$temp);
    $scripts = scandir('pages/'.$page);
	for($i=2; $i<count($scripts); $i++)
	{
	 include("pages/$page/scripts/$scripts[$i]");
	}
    return;
   }
   else if($page=='library')
   {
    if($dir!='modal')
    {
     self::error('missing_modal',CURRENT_PAGE,CURRENT_VIEW,'_addScript',__LINE__,$scripts);
    }
    $scripts = strtolower($scripts);
    $scripts = str_replace('.',DS,$scripts);
    $scripts = str_replace('modal','modals',$scripts);
	$scripts .= '.php';
	// echo 'modals: '.$scripts;
    if($dir=='modal' && $temp[2]=='*')
	{
	 $list = scandir('library/');
	 for($i=2; $i<count($list); $i++)
	 {
	  if(is_file('library/'.$list[$i]) && @end(explode('.',$list[$i]))=='php')
	   require_once "library/".$list[$i];
	 }
	}
	else if($dir=='modal')
	 require_once $scripts;
   }
   else if($page=='plugins')
   {
    $list = scandir($page.DS);
    if(!in_array($dir,$list))//invalid php plugin name
    {
     self::error('missing_resource',CURRENT_PAGE,CURRENT_VIEW,'_addScript',__LINE__,$scripts);
    }
    unset($temp[1]);
    if(isset($temp[2]) && $temp[2]=='*')
    {
     $list = scandir($page.DS.$dir.DS);
     for($i=2; $i<count($list); $i++)
      require ('plugins'.DS.$dir.DS.$list[$i]);
    }
    else
     // require ('plugins'.DS.$dir.DS.$temp[2].'.php'); 
     require_once ('plugins'.DS.$dir.DS.'index.php'); 
   }
   else
    require_once ("pages/$page/scripts/$filename");
  }
 }

 function _loadView($viewpath)
 {
  $keys = explode('.',$viewpath);
  if(!count($keys))
   return false;
  $pagename = $keys[0]=='this'?PAGE:$keys[0];
  $file = "pages/$pagename/controllers/layout.php";
  
  include_once $file;//pages/pagename/controllers/layout.php
  $layoutClass = $pagename."Layout";//layout class for page, eg, defaultLayout
  $layoutObj = new $layoutClass;//instance of the above class
  //now, we use data in $layout_obj to assign to current object
  // $this->view_sections = $layoutObj->layout_chunks;//get the layout sections set
  $chunks = $layoutObj->layout_chunks;//get the layout sections set
  for($i=1; $i<count($keys); $i++)
  {
   if(!isset($chunks[$keys[$i]]))
   {
    ob_end_flush();
    echo 'invalid view key '.$keys[$i].' given to include view at '.implode('.',$keys);
    die;
   }
   else
    $chunks = $chunks[$keys[$i]];
  }
  // print_r($chunks);die;
  self::loadView($chunks,$pagename);
 }
 
 function loadView($chunks,$page)
 {
  foreach($chunks as $key=>$chunk)
  {
   $str = '<div ';
   foreach($chunk as $index=>$arr)
   {
    if($index!='views' && $index!='resources')
     $str .= "'$index'='$arr'";
    else if($index=='resources')
     self::_addScript($arr);
    else if($index=='views')
    {
     if(!is_array($arr) || (isset($arr['views']) && !is_array($arr['views'])))
     {
      $temp = isset($arr['views'])?$arr['views']:$arr;
      $temp = explode('.',$temp);
      $ext = end($temp);
      global $view_filetypes;
      if(!in_array($ext,$view_filetypes))
       self::_loadView($arr);
      else
      {
       $view = $index.'View';
       $file = "pages/$pagename/controllers/view.php";
       if(file_exists($file))
        include $file;
       $class = $pagename.'Views';
       $obj = new $class;
       if(method_exists($obj,$view))
        $obj->$view();
       include $arr;
      }
     }
    }
   }
   $str .= '>';
  }
 /* 
  $keys_exist = isset($chunks['views']) || isset($chunks['class']) || isset($chunks['resources']);
  if(count($chunks) $keys_exists)
  {
   foreach($chunks as $key=>$chunk)
   {
    if($key=='views')
    {
     if(is_array($chunk))
      self::loadView($chunk);
     else
     {
      $temp = explode('.',$chunk);
      $ext = end($temp);
      global $view_filetypes;
      if(in_array($ext,$view_filetypes))
      {
       $path = "pages/$pagename/views/$chunk";
       return (include $path);
      }
      else
       self::_loadView($chunk);//
     }
    }
   }
  } */
 }
}

class HTML extends Layout
{
}

class LayoutSetup
{
    var $layout_chunks;
    public function __construct($layout_arr=false)
    {
        $this->layout_chunks = $layout_arr;
    }
}

function _getScript($path)//doesn't manipulate the path
{
 $path = str_replace('.',DS,$path);
 $chunks = explode(DS,$path);
 $file = $chunks[count($chunks)-2].'.'.$chunks[count($chunks)-1];
 // echo $file;
 array_splice($chunks,count($chunks)-2,2);
 // print_r($chunks);
 $path = implode(DS,$chunks);
 $temp = explode(':',$path);//'library:path':for library paths; otherwise, general notation
 if(count($temp)>1)
  $path = 'library'.DS.implode(DS,$temp);
 else
 {
  $path = implode(DS,$temp);
  $path = 'pages'.DS.$path;
 }
 $path .= DS.$file;
 require $path;
}

function _import($scripts)
{
 global $layout;
 $layout->_import($scripts);
 return;
 if(is_array($scripts))
 {
  foreach($scripts as $script)
  {
   $path = $from? "$from.$script" : $script;
   _import($path);
  }
  return;
 }
 else
 {
  $scripts = str_replace(DS,'.',$scripts);
  $temp = explode('.',$scripts);
  $page = $temp[0];//page name
  unset($temp[0]);
  $dir = isset($temp[1]) ?$temp[1] : false;
  if($page=='library')
  {
   $scripts = strtolower($scripts);
   $scripts = str_replace('.',DS,$scripts);
   $scripts = str_replace('modal','modals',$scripts);
   $scripts .= '.php';
   // echo 'modals: '.$scripts;
   if($dir=='modal' && $temp[2]=='*')
   {
    $list = scandir('library/');
    for($i=2; $i<count($list); $i++)
    {
     if(is_file('library/'.$list[$i]) && @end(explode('.',$list[$i]))=='php')
      require_once "library/".$list[$i];
    }
   }
   else if($dir=='modal')
    require_once $scripts;
  }
  else if($page=='plugins' || $page=='plugin')
  {
   $temp = implode(DS,$temp).DS.'index.php';
   require_once PLUGIN.$temp;
  }
  else
  {
   $filename = implode('.',$temp);
   require_once ("pages/$page/scripts/$filename");
  }
 }
}

function alert($str)
{
 @ob_end_clean();
 echo "<script>console.log('Got this for alert: $str')</script>";die;
}