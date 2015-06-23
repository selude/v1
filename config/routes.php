<?php
global $routes;

$routes['home'] = 'default/index';
$routes['contact-us'] = 'default/contact';
$routes['profile/$1'] = 'user/profile/id/$1';
$routes['profile/edit'] = 'user/profile/id/$1/tab/$2';