<?php

// return [
//   '/' => 'controllers/home.php',
//   '/listings' => 'controllers/listings/index.php',
//   '/listings/create' => 'controllers/listings/create.php',
//   // '404' => 'controllers/error/404.php',
// ];

$router->get('/', 'HomeController@index');
$router->get('/listings', 'ListingsController@index');
$router->get('/listings/create', 'ListingsController@create');
$router->get('/listings/edit/{id}', 'ListingsController@edit');
$router->get('/listings/{id}', 'ListingsController@show');


$router->post('/listings', 'ListingsController@store');
$router->put('/listings/{id}', 'ListingsController@update');
$router->delete('/listings/{id}', 'ListingsController@destory');


$router->get('/auth/register', 'UserController@create');
$router->get('/auth/login', 'UserController@login');


$router->post('/auth/register', 'UserController@store');

// $router->get('/', 'controllers/home.php');
// $router->get('/listings', 'controllers/listings/index.php');
// $router->get('/listings/create', 'controllers/listings/create.php');
// $router->get('/listing', 'controllers/listings/show.php');
