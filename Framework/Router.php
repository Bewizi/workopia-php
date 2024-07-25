<?php

// $routes = require basePath('routes.php');

// if (array_key_exists($uri, $routes)) {
//   require basePath($routes[$uri]);
// } else {
//   http_response_code(404);
//   require basePath($routes['404']);
// }

namespace Framework;



use App\Controllers\ErrorController;

class Router
{
  protected $routes = [];

  /**
   * Add a new route
   * 
   * @param string $method
   * @param string $uri
   * @param string $action
   * @return void
   */
  public function registerRoute($method, $uri, $action)
  {
    list($controller, $controllerMethod) = explode('@', $action);

    // inspectAndDie($controllerMethod);
    $this->routes[] = [
      'method' => $method,
      'uri' => $uri,
      'controller' => $controller,
      'controllerMethod' => $controllerMethod
    ];
  }

  /**
   * Add a GET route
   * 
   * @param  string $uri
   * @param  string $controller
   * @return void
   */

  public function get($uri, $controller)
  {
    $this->registerRoute('GET', $uri, $controller);
  }



  /**
   * Add a POST route
   * 
   * @param  string $uri
   * @param  string $controller
   * @return void
   */

  public function post($uri, $controller)
  {
    $this->registerRoute('POST', $uri, $controller);
  }



  /**
   * Add a PUT route
   * 
   * @param  string $uri
   * @param  string $controller
   * @return void
   */

  public function put($uri, $controller)
  {
    $this->registerRoute('PUT', $uri, $controller);
  }


  /**
   * Add a DELETE route
   * 
   * @param  string $uri
   * @param  string $controller
   * @return void
   */

  public function delete($uri, $controller)
  {
    $this->registerRoute('DELETE', $uri, $controller);
  }

  // /**
  //  * Load error page
  //  * @param int $httpCode
  //  * 
  //  * @return  void
  //  */
  // public function error($httpCode = 404)
  // {
  //   http_response_code($httpCode);
  //   loadView("error/{$httpCode}");
  //   exit;
  // }

  /**
   * Route the request
   * 
   * 
   * @param string $uri
   * @param string $method
   * @return void 
   */
  public function route($uri)
  {
    $requestMethod = $_SERVER['REQUEST_METHOD'];

    // Check for _method input
    if ($requestMethod === 'POST' && isset($_POST['_method'])) {
      //Override the request method with the value of _method
      $requestMethod = strtoupper($_POST['_method']);
    }

    foreach ($this->routes as $route) {

      // Split the current URI into segements
      $uriSegments = explode('/', trim($uri, '/'));
      // inspectAndDie($uriSegments);

      // Split the route URI into segments
      $routeSegments = explode('/', trim($route['uri'], '/'));

      // inspect($routeSegments);

      // compare each segments
      $match = true;

      // if the number of segments match
      if (count($uriSegments) === count($routeSegments) && strtoupper($route['method']) === $requestMethod) {
        // create a params
        $params = [];

        $match = true;

        // loop through the URI segments
        for ($i = 0; $i < count($uriSegments); $i++) {
          // If the uri's do not match and there is no param
          if ($routeSegments[$i] !== $uriSegments[$i] && !preg_match('/\{(.+?)\}/', $routeSegments[$i])) {
            $match = false;
            break;
          }

          // Check for the param and add to $parms array
          // If the uri's do not match and there is a param
          if (preg_match('/\{(.+?)\}/', $routeSegments[$i], $matches)) {
            // inspectAndDie($matches[1]);
            $params[$matches[1]] = $uriSegments[$i];
            // inspectAndDie($params);
          }
        }
        if ($match) {
          $controller = 'App\\Controllers\\' . $route['controller'];
          $controllerMethod = $route['controllerMethod'];

          // Instiate the controller and call the method
          $controllerInstance = new $controller();
          $controllerInstance->$controllerMethod($params);
          return;
        }
      }


      // if ($route['uri'] === $uri && $route['method'] === $method) {
      //   // require basePath('App/' . $route['controller']);

      //   // Extract controller and controller method
      //   $controller = 'App\\Controllers\\' . $route['controller'];
      //   $controllerMethod = $route['controllerMethod'];

      //   // Instiate the controller and call the method
      //   $controllerInstance = new $controller();
      //   $controllerInstance->$controllerMethod();
      //   return;
      // }
    }
    ErrorController::notFound();
  }
}
