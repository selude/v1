<?php
$this->_import('library.modal.selude');
class adminModal extends SeludeModal
{
 function index($data)
 {
  $field = count(explode('@',$data['username']))>1?'email':'username';
  $$field = $data['username'];
  $this->select('*')->from('s_admins')->where("$field = '${$field}'");
  $res =  $this->getEntity();
  $result = array();
  if($res)
  {
   if($res->password == md5($data['password']))
   {
    $result['success'] = true;
	$result['message'] = 'Successfully logged in';
	session::set('admin',$data['username']);
   }
   else
   {
    $result['success'] = false;
	$result['err_code'] = 'cred_err';
	$result['message'] = 'Invalid password';
   }
  }
  else
  {
   $result['success'] = false;
   $result['err_code'] = 'auth_err';
   $result['message'] = 'Invalid username';
  }
  return $result;
 }

 function pages($data)
 {
  $result = array();
  if(isset($data['pagename']))//create page
  {
   if(empty($data['pagename']) || !trim($data['pagename']))
   {
    $result['success'] = false;
    $result['message'] = 'Invalid pagename!';
   }
   else
   {
    $pagename = strtolower(str_replace(' ','_',$data['pagename']));
	$res = Page::createComponents($pagename);
    if(!$res['success'])
    {
     $result['success'] = false;
     $result['message'] = $res['message'];
    }
    else
    {
	 if(!file_exists("layouts/$pagename.php"))//skip for previously created pages
	 {
	  file_put_contents("layouts/$pagename.php",file_get_contents('library/templates/page.php'));
	  $file = "layouts/$pagename.php";
	  file_put_contents($file,str_replace('class page',"class $pagename",file_get_contents($file)));
	 }
	 $file = "pages/$pagename/viewController.php";
	 file_put_contents($file,str_replace(' page'," $pagename",file_get_contents($file)));
	 // Page::createMeta($pagename);
	  
     $result['success'] = true;
     $result['message'] = 'Page created successfully!';
    }
   }
  }
  else if(isset($data['viewName']))//create view
  {
   $data['viewName'] = sVar::filterVar($data['viewName']);
   $view['name'] = str_replace('view','',$data['viewName']);
   $view['title'] = str_replace('View','',sVar::filter($data['viewLabel']));
   
   // print_r($view);die;
   $result['success'] = false;
   if(!$view['name'])
    $result['message'] = 'Invalid view name!';
   else if(empty($view['title']))
    $result['message'] = 'Invalid view label!';
   else
   {
    $result['success'] = true;
    $pagename = sVar::getVal('page');
	// echo $pagename;die;
    $view['edit'] = sVar::filterVar($data['edit']);
    $meta['title'] = $view['title'];
    $meta['description'] = $data['viewDesc'];
    
	// print_r($meta);die;
	$error = false;
	$orig_meta = array();
	if($view['edit'])//rename the viewName to this new view
	{
	 if(file_exists('meta'.DS.$pagename.DS.$view['name'].'.ini'))
	 {
	  $orig_meta = parse_ini_file('meta'.DS.$pagename.DS.$view['name'].DS.'.ini',true);
	  $orig_meta['author'] = $meta['author'];
	  $orig_meta['title'] = $meta['title'];
	  $orig_meta['description'] = $meta['description'];
	 }
	 Page::renameView($pagename, $view['edit'], $view['name']);
	}
	else
	{
	 $result = Page::createView($pagename,$view['name']);
	 $error = isset($result['success'])?true:false;
	 $orig_meta = &$meta;
	}
	if($error)
	 return $result;
    
	$def_author = $data['defAuthor'];
	if($def_author)//get default author name from index view of $pagename
	{
	 if(file_exists('meta/default/index.ini'))
	 {
	  $temp = parse_ini_file('meta/default/index.ini',true);
	  $orig_meta['author'] = $temp['author'];
	 }
	}
	else
     $orig_meta['author'] = $data['viewAuthor'];
	
	if(isset($_FILES['viewFile']))
	{
	 $image_path = time().'_'.sVar::filterVar($_FILES['viewFile']['name']);
	 $image_path = "pages/$pagename/resources/images/$image_path";
	 move_uploaded_file($_FILES['viewFile']['tmp_name'],$image_path);
	 $orig_meta['image'] = $image_path;
	}
	// print_r($meta);die;
	$str = '';
	foreach($orig_meta as $key=>$val)//save old meta
	{
	 $str .= $key.' = "'.$val.'"'."\r\n";
	}
	$metafile = 'meta'.DS.$pagename.DS.$view['name'].'.ini';
	// print_r($orig_meta);die;
	unlink($metafile);
	file_put_contents($metafile,$str);
    $result['success'] = true;
    $result['message'] = 'View added to list!';
   }
  }
  session::set('popup',$result);
  return $result;
 }
}
?>