<?php
require_once 'DBA.php';
class Page
{
 public static function getList()
 {
  $arr = array();
  $list = scandir('pages'.DS);
  foreach($list as $item)
  {
   if(is_dir('pages'.DS.$item) && $item!='.' && $item!='..' )
   //&& $item!='admin')
    $arr[] = $item;
  }
  return $arr;
 }
 
 public static function getMeta($pagename)
 {
  $ini = 'meta'.DS.$pagename.DS.'index.ini';
  if(file_exists($ini))
  {
   $meta = parse_ini_file($ini,true);
   return $meta;
  }
  else
   return false;
 }
 
 public static function getViewMeta($pagename,$viewname)
 {
  $db = new DB;
  $temp = $db->fetchAll("SELECT * FROM s_meta m JOIN s_views v ON v.id = m.view_id AND v.name = '$viewname' JOIN s_pages p ON p.id = v.page_id AND p.name = '$pagename'");
  if(count($temp))
  {
   $meta = array();
   for($i=0 ; $i<count($temp); $i++)
   {
    $meta[$temp[$i]['tag_label']] = $temp[$i]['tag_value'];
   }
   return $meta;
  }
  $ini = 'meta'.DS.$pagename.DS.str_replace('View','',$viewname).'.ini';
  if(file_exists($ini))
  {
   $meta = parse_ini_file($ini,true);
   $q = "SELECT id FROM s_pages WHERE name='$pagename'";
   $res = $db->fetch($q);
   $pageid = $res['id'];
   $q = "SELECT id FROM s_views WHERE name='$viewname' and page_id=$pageid";
   $res = $db->fetch($q);
   if(!count($res))
   {
    $res['id'] = $db->insert("INSERT INTO s_views (page_id,name) VALUES($pageid, '$viewname')");
   }
   $viewid = $res['id'];
   foreach($meta as $key=>$value)
   {
    $q = "INSERT INTO s_meta (page_id,view_id,tag_label,tag_value) VALUES ($pageid, $viewid, '$key', '$value')";
	$db->insert($q);
   }
   return $meta;
  }
  else
   return false;
 }
 
 public static function getDirectories($pagename,$treeview=false)
 {
  $list = self::getSubDirList('pages'.DS.$pagename,$treeview);
  // $list = self::processList($list);
  return $list;
 }
 
 public static function getSubDirList($item,$treeview = false)
 {
  if(is_dir($item) && ($item!='.'||$item!='..'))
  {
   $res = array();
   if($treeview)
   {
    echo '<div class="one_item">
			<div class="one-joint open">
			</div><div class="folder-box folder-open"></div>'.@end(explode(DS,$item)).'
			</div>';
    echo '<div class="list-container">';
   }
   $files = scandir($item);
   // print_r($files);
   $key = @end(explode(DS,$item));
   for($i=2; $i<count($files); $i++)
   {
    $res[$key][] = self::getSubDirList($item.DS.$files[$i],$treeview);
   }
   if($treeview)
    echo '</div>';
   return $res;
  }
  else
  {
   if($treeview)
   {
    $ext = @end(explode('.',$item));
    echo '<div class="one_item">
			<div class="file">
				<div class="file-box '.$ext.'"></div>'
				.@end(explode(DS,$item)).
			'</div>
		  </div>';
   }
   return $item;
  }
 }
  
 public static function listView($list)
 {
  if(is_array($list))
  {
   $key = key($list);
   if(!is_numeric($key))
   {
    echo '<div><div class="one-joint">-</div>'.$key.'</div>';
	echo '<div class="list-container">';
   }
   foreach($list as $key=>$item)
   {
    if(!is_array($item))
	 echo '<div><div class="one-joint">-</div>'.$key.'</div>';
	else
	{
	 self::listView($item);
	}
   }
   if(!is_numeric($key))
    echo '</div>';
  }
  else
  {
   echo '<div><div class="one-joint">-</div>'.@end(explode(DS,$list)).'</div>';
  }
 }

 public static function getViewsList($pagename)
 {
  $file = 'pages/'.$pagename.'/viewController.php';
  require_once $file;
  $classname = $pagename.'Layout';
  $list = get_class_methods($classname);
  
  $file = "pages/$pagename/layoutController.php";
  require_once $file;
  $pre_defined = get_class_methods('Layout');
  $result = array();
  foreach($list as &$method)
  {
   if(!in_array($method,$pre_defined))
	$result[] = $method;
  }
  // print_r($list);
  return $result;
 }

 public static function createComponents($pagename,$path=null)
 {
  if(!$path)
  {
   $path = 'library/templates/page/';
  }
  $dest = str_replace('library/templates/page','pages/'.$pagename,$path);
  if(!is_dir($path))
  {
   $res  = file_put_contents($dest,file_get_contents($path));
   if(!$res)
   {
    $result['success'] = false;
    $result['message'] = 'An error occurred while copying the file to <b>'.$dest.'</b>';
	return $result;
   }
  }
  else
  {
   $res = mkdir($dest,0777);
   if(!$res)
   {
    $result['success'] = false;
    $result['message'] = 'An error occurred while creating the direcoty '.$dest;
	return $result;
   }
   $list = scandir($path);
   for($i=2; $i<count($list); $i++)
   {
    self::createComponents($pagename,$path.DS.$list[$i]);
   }
  }
  return array('success'=>true);
 }
 
 public static function createMeta($pagename)
 {
  $metafile = "meta/$pagename";
  mkdir($metafile,0777);
  include_once 'library/PHProp-master/PHProp.php';
  $meta = parse_ini_file('templates/meta.ini',true);
  global $configuration;
  // $meta->title = $pagename.' |  Home ';//.$configuration->app->name;
  $meta->title = $configuration->app->name.' | '.$pagename.' - Home';
  $meta = str_replace('{page}',$pagename,$meta);
  $db = new DB;
  $res = $db->fetch("SELECT id FROM s_pages WHERE name = '$pagename'");
  $page_id = $res['page_id'];
  $view_id = $db->insert("INSERT INTO s_views (page_id, name) VALUES($page_id, 'index')");
  
  // $fp = fopen($metafile.'/index.ini','w+');
  foreach($meta as $key=>$value)
  {
   $db->insert("INSERT INTO s_meta (page_id, view_id, tag_label, tag_value) VALUES($page_id, $view_id, '$key', '$value')");
   // fprintf($fp,"$key = $value\r\n");
  }
  // fclose($fp);
 } 

 public static function createView($for_page,$viewname)//creates fresh views
 {  
  $file = "pages/$for_page/viewController.php";
  $vc = file($file);
  // print_r($vc);die;
  require_once $file;
  $class = $for_page.'Page';
  $obj = new ReflectionClass($class);
  $viewMethod = $viewname.'View';
  $result = array();
  $result['success'] = true;
  if($obj->hasMethod($viewMethod))
  {
   $result['success'] = false;
   $result['message'] = "A view with name '$viewMethod' already exists. Please delete it first to add a view with same name. In case you intend to rename it, try using 'Edit details' option.";
   return $result;
  }
  $methods = $obj->getMethods();
  $parent = true;
  if(!$obj->hasMethod('indexView'))
  {
   $start_line = $obj->getMethod('__construct')->getEndLine();
  }
  else
  {
   $start_line = $obj->getMethod('indexView')->getEndLine();
  }
  $content_ahead = array_slice($vc,$start_line);//content to be appended at the end.
  $vc = array_slice($vc,0,$start_line);//content to be prepended
  
  $template = "library/templates/viewTemplate.php";
  $content = file($template);
  foreach($content as &$line)
  {
   $line = str_replace(' viewname',' '.$viewname,$line);
  }
  $vc = array_merge($vc, $content);
  $vc = array_merge($vc, $content_ahead);
  file_put_contents("pages/$for_page/viewController.php",$vc);
  
  $file = "layouts/$for_page.php";
  $layout = file($file);
  require_once $file;
  $classname = $for_page.'Layout';
  $class = new ReflectionClass($classname);
  $start = $class->getMethod('__construct')->getStartLine();
  $end = $class->getMethod('__construct')->getEndLine();
  
  $tempObj = new $classname;//instance of layout class object
  $tempObj->layout_chunks[$viewname] = isset($tempObj->layout_chunks[$viewname]) ?$tempObj->layout_chunks[$viewname] :$tempObj->layout_chunks['index'];
  $temp = array_slice($layout,0,$start+1);
  foreach($tempObj->layout_chunks as $key=>$layout_item)
   $temp[] = "\r\n   ".'$view["'.$key.'"] = '.printr($layout_item);
  $temp[]  = "\r\n   ".'$this->pushLayout($view);'."\r\n";
  
  $layout = array_slice($layout, $end-1);
  $temp = array_merge($temp,$layout);
  file_put_contents($file,$temp);
  // echo $result;die;
  return $result;
 }
 
 public static function renameView($for_page, $old_name, $new_name)
 {
  $file = "pages/$for_page/viewController.php";
  $content = file_get_contents($file);
  $content = str_replace($old_name.'View', $new_name.'View', $content);
  $content = str_replace(' '.$old_name, ' '.$new_name, $content);
  unlink($file);
  file_put_contents($file,$content);
  if(file_exists("meta/$for_page/$old_name.ini"))
   rename("meta/$for_page/$old_name.ini","meta/$for_page/$new_name.ini");//rename meta file too
 }
}

class Project
{
 public static function getList()
 {
  $dir = '../';
  $list = scandir($dir);
  // print_r($list);
  $project_list = array();
  foreach($list as $key=>$item)
  {
   if($key== '.' || $key== '..')
    continue;
   if(is_dir($dir.$item))//search for project in this dir
   {
    $config_file = $dir.$item.DS.'config.ini';
    // echo $config_file;
    if(file_exists($config_file) && $item!=APP_NAME)//a config file is there;skip for self
    {
     $arr = parse_ini_file($config_file,true);
  // print_r($arr);
     if(isset($arr['about']['namespace']) && 
	    strtolower($arr['about']['namespace']) == 'selude')
     {
      $temp = array();
      $temp['title'] = $arr['about']['label'];
      $temp['link'] = str_replace(' ','_',$item);
      $project_list[] = $temp;
     }
    }
   }
   // else
    // echo '<br>'.$item;
  }
  $result = count($project_list)>0?$project_list:false;
  return $result;
 }
 
 public static function getDetails($proj_name)
 {
  $file = '../'.$proj_name.'/config.ini';
  $result = parse_ini_file($file, true);
  return $result;
 }
}

function printr($arr)
{
 $str = '';
 static $list = '';
 if(is_array($arr))
 {
  $str = 'array(';
  foreach($arr as $key => $item)
  {
   $list[] = $key;
   $str .= "'$key' => ";
   if(is_array($item))
   {
    $str .= printr($item).',';
   }
   else
    $str .= "'$item',";
   array_pop($list);
  }
  $str = trim($str,',').')';
 }
 else
  $str .= "'$arr',";
 $str = trim($str,',');
 if(!count($list))
  $str .= ';';
 return $str;
}