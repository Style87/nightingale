<?php

class Router
{
  protected $uriSegments = [];
  protected $controllerString;
  protected $action = 'displayIndexAction';
  protected $controller;

  public function dispatch()
  {
    $controller = $this->controller;
    $controller->{$this->action}();
  }

  public function getController()
  {
    return $this->controller;
  }

  public function getControllerString()
  {
    return $this->controllerString;
  }

  /**
   * Singleton
   * @return Router
   */
  static public function instance() {
      static $instance;
      if (!($instance instanceof self)) {
          $instance = new self();

          $_GET['q'] = str_replace('/index.php', '', $_GET['q']);

          // Set up uri
          $instance->uriSegments = explode('/', $_GET['q']);

          $instance->controllerString = ucfirst($instance->uriSegments[0]) . 'Controller';
          $controllerString = $instance->controllerString;
          if (class_exists($controllerString))
          {
            $instance->controller = $controllerString::instance();
            if (count($instance->uriSegments) == 2 && method_exists($instance->controller, $instance->uriSegments[1].'Action'))
            {
              $instance->action = $instance->uriSegments[1].'Action';
            }
            else if (count($instance->uriSegments) == 2)
            {
              $instance->controller->id = trim(urldecode($instance->uriSegments[1]));
            }
            else if (count($instance->uriSegments) == 3 && method_exists($instance->controller, $instance->uriSegments[2].'Action'))
            {
              $instance->controller->id = urldecode($instance->uriSegments[1]);
              $instance->action = $instance->uriSegments[2].'Action';
            }
          }
          else
          {
            $instance->controllerString = 'DashboardController';
            $instance->controller = DashboardController::instance();
          }

          $instance->controller->router = $instance;
      }

      return $instance;
  }
}
