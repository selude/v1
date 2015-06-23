<?php
class defaultViews extends Layout
{
 function __construct($def_view=true)
 {
  $this->renderView['login'] = false;
  $this->renderView['ajax'] = false;
 }
 
 public function indexView()
 {
  $this->setTitle('Welcome to Simran Trading Company!');
 }
 
 public function contactView()
 {
  if(post)
  {
    $form_submitted = $this->processData();//calls up the modal function for current view
    //or use, $this->execute()
    session::set('form_done',$form_submitted);
    $form_submitted? $this->redirect('contact-us',true):null;
  }
  $this->setTitle('Contact Us');
 }
 
 public function aboutView()
 {
  $this->setTitle('About Us');
 }
 
 public function productsView()
 {
  $this->setTitle('Products we deal in');
 }
}