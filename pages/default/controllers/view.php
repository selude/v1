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
  $this->setTitle('Welcome to My Company!');
  $meta['keyword'] = 'specify keywords for the page. This is used in SEO crawling';
  $meta['description'] = 'Set your homepage description here. This is displayed on search engines when they list and display your page there.';
  $this->setMetaTags($meta);
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
  $meta['keyword'] = 'specify keywords for the page. This is used in SEO crawling';
  $meta['description'] = 'Set your homepage description here. This is displayed on search engines when they list and display your page there.';
 }
 
 public function aboutView()
 {
  $this->setTitle('About Us');
  $meta['keyword'] = 'specify keywords for the page. This is used in SEO crawling';
  $meta['description'] = 'Set your homepage description here. This is displayed on search engines when they list and display your page there.';
 }
}