<?php
class defaultLayout extends LayoutSetup
{
    function __construct()
    {
     $view["index"] = 
        array('views' => array('top_menu' => array('views' => 'this.top'),
							   'middle_content' => 
                                    array('class' =>'home_page clearfix',
                                          'resources'=> array('default.css.guest','default.js.index'),
                                          'views' => 'homepage.phtml'),
							   'footer'=>array('views'=>'this.footer')));
                               
     $view["contact"] = 
        array('views' => array('top_menu' => array('views' => 'this.top'),
							   'middle_content' => 
                                    array('class' =>'contact',
                                          'resources'=> array('default.css.guest','default.js.index'),
                                          'views' => 'contact.phtml'),
							   'footer'=>array('views'=>'this.footer')));
                               
     $view["about"] = 
        array('views' => array('top_menu' => array('views' => 'this.top'),
							   'middle_content' => 
                                    array('class' =>'about_page',
                                          'resources'=> array('default.css.guest','default.js.index'),
                                          'views' => 'about.phtml'),
							   'footer'=>array('views'=>'this.footer')));
     $view["products"] = 
        array('views' => array('top_menu' => array('views' => 'this.top'),
							   'middle_content' => 
                                    array('class' =>'product_page',
                                          'resources'=> array('default.css.guest','default.js.index','default.js.animation'),
                                          'views' => 'product.phtml'),
							   'footer'=>array('views'=>'this.footer')));
     $view["top"] = array('class' => 'clearfix',
						  'resources'=> array('default.js.jquery1.10.2','default.css.icomoon'),
						  'views' => 'top.phtml');
     $view['footer'] = array('views' => 'footer.phtml');
     parent::__construct($view);
    }
}