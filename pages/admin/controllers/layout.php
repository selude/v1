<?php
class adminLayout extends LayoutSetup
{
    function __construct()
    {

       $view["index"] = 
        array('views' => array('homebar' => array('views' => 'this.top'),
                               'content' => array('class' => 'container-fluid',
                                                 'views' => 'homepage.phtml')));
       $view["ceditor"] = 
        array('views' => array('toppane' => array('views' => 'this.top'),
                               'bottom' =>  array('class' => 'container-fluid',
                                          'views' => 'ce_body.phtml')));
       $view["pages"] = 
        array('views' => array('homebar' => array('views' => 'this.top'),
                               'content' => array('class' => 'container-fluid',
                                          'views' => 'pages_body.phtml')));
       $view["db"] =
        array('views' =>array('top' => array('views' => 'this.top'),
                              'leftmenu' => array('views' => 'this.body.leftmenu'),
                              'rightpage' => array('views' => 'dbpage.phtml')));
       $view["top"] = 
        array('resources'=>array('this.css.index', 'this.css.dashboard', 'this.css.editor', 'this.css.loginpage', 'this.css.pages', 'default.js.jquery1.10.2', 'this.css.treeview', 'this.js.pages'),
              'class' => 'navbar navbar-inverse navbar-fixed-top',
                         'role' => 'navigation','views' => 'top.phtml');
       $view["body"] = 
        array('class' => 'row',
              'views' => array('leftmenu' => 
                                    array('class' => 'col-sm-3 col-md-2 sidebar',
                                          'views' => 'leftmenu.phtml'),
                               'rightpage' => 
                                    array('class' => 'col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main',
                                          'views' => 'rightpage.phtml')));
       $view["ajax"] = 
        array('views' => array('homebar' => array('views' => 'this.top','resources'=>array('default.css.*')),
                              'content' => array('class' => 'container-fluid',
                                                 'views' => 'homepage.phtml')));
       parent::__construct($view);
    }
}
?>