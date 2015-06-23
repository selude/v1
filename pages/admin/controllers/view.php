<?php
class adminViews extends Layout
{
 var $project_name;
 var $view;
 
 function __construct($def_view=true)
 {
  $this->renderView['ajax'] = false;
  global $configuration;
  // $this->dataController = new adminData;
  $this->project_name = $configuration['about']['label']; 
 }
 
 public function indexView()
 {
  $this->setTitle($this->project_name.': Administrator');
 }

 public function ajaxView()
 {
  // $this->_addScript('default.css.*');
  $task = $this->POST('task');
  switch(strtolower($task))
  {
   case 'deletepage':
	$pagename = $this->POST('page');
	if(!$pagename)
		echo 'Invalid delete request';
	else
		echo 'Deleted page '.$pagename;
   break;
  }
 }
 
 public function pagesView()
 {
  $admin = session::get('admin',false);
  if(!$admin)
  {
   session::set('popup','You are trying to access a secure page. Please login first.');
   $this->redirect('this.index?error=unauth_user');
  }
  // $this->includePageResources('default');
  // $this->includePageResources();
  $page = sRequest::_get('page');
  if($page)
	$this->setTitle($this->project_name.' | Page Views');
  else
	$this->setTitle($this->project_name.' | Pages');
 }

 public function dbView()
 {
  // $this->_addScript('admin.*');
  // $this->redirect('projects');
  $admin = session::get('admin',false);
  if(!$admin)
  {
   session::set('popup','You are trying to access a secure page. Please login first.');
   $this->redirect('admin?error=unauth_user');
  }
  $this->includePageResources('default');
  $this->setTitle($this->project_name.' | Database');
 }
 
 public function ceditorView()
 {
  // $this->_addScript('admin.*');
  $admin = session::get('admin',false);
  if(!$admin)
  {
   session::set('popup','You are trying to access a secure page. Please login first.');
   $this->redirect('admin?error=unauth_user');
  }
  $this->includePageResources('default');
  $this->setTitle($this->project_name.' | Code Editor');
 }
}
?>