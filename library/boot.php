<?php
 // error_reporting(E_ALL);
 require_once 'selude.html.php';
 Configuration::setupVariables();
 global $layout;
 $layout = new Layout(true);
 
 $config = new Configuration;//from library/settings.php (no constructor)
 $config->setupVariables(); 
 
 $url = new URL;//from settings.php
 
 $url->getPage();//gives page name, view name
 // print_r($url);die;
 if(PAGE=="Error404")
  $layout->error("error404",$url->page,$url->view,'undefined',__LINE__);
 else
 {
  $dir = $url->page.DS.'resources'.DS;
  // echo PAGE.' '.VIEW;die;
  $viewController = 'pages'.DS.$url->page.DS.'/controllers/view.php';
  if(!file_exists($viewController))
	$layout->error('missing_layout_controller',$url->page,false,false,__LINE__);
  include_once $viewController;//include view controller for this page
  /* Each page has it's different layoutController, as each page can have different layout */
  $view_class = $url->page."Views";//creates instance of ,eg, class home, from home/view/index.php
  $view_obj = new $view_class(true);//call constructor of pages's object
  $view = $url->view."View";//create instance of view method for this class
  $renderView = $view_obj->renderView;
  $render_view = isset($renderView[VIEW]) ? $renderView[VIEW] : true;
  // print_r($_GET);die;
  if(method_exists($view_obj,$view) && $render_view)
  {
   $view_obj->setLayout();//set layout this page
   $view_obj->$view();//call view method for this class instance
   $view_obj->includeLayout();//call view method for this class instance
  }
  else if(!$render_view && defined('VIEW'))
  {
   sRequest::getURLData();
   $view_obj->_dco = $view_obj->_getDCO();
   // print_r($view_obj);
   $view_obj->$view();
  }
  else
  {      
    defined ('VIEW_ERROR') || define ('VIEW_ERROR',true);
    // echo 'Error in view: '.CURRENT_VIEW;die;
    // $layout->error("undefined_view"); 
  }
 }