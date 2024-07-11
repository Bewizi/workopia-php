<?php
require '../helpers.php';
require basePath('Router.php');
require basePath('Database.php');

// $config = require basePath('config/db.php');

// $db = new Database($config);


// require basePath('views/home.view.php');

// loadView('home');

// Instaiating the router
$router = new Router();

// Get routes
$routes = require basePath('routes.php');

// Get current URI and HTTP method 
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

//Route the request
$router->route($uri, $method);
// inspectAndDie($uri);
