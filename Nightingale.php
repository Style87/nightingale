<?php

/**
 * Copyright (c) 2016 Lucas Hartzell
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @package Nightingale
 * @version 0.1
 * @author Lucas Hartzell
 * @copyright Lucas Hartzell 2016
 */

class Nightingale
{
  protected $_readonly = true;

  protected $_database;

  protected $_action = "index";
  protected $_adapter;

  protected $git;
  public $id = NULL;

  public function authenticate() {
      if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
          $authorization = $_SERVER['HTTP_AUTHORIZATION'];
      } else {
          if (function_exists('apache_request_headers')) {
              $headers = apache_request_headers();
              $authorization = $headers['HTTP_AUTHORIZATION'];
          }
      }

      list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($authorization, 6)));
      if (strlen(NIGHTINGALE_USERNAME) && strlen(NIGHTINGALE_PASSWORD) && (!isset($_SERVER['PHP_AUTH_USER']) || !($_SERVER['PHP_AUTH_USER'] == NIGHTINGALE_USERNAME && $_SERVER['PHP_AUTH_PW'] == NIGHTINGALE_PASSWORD))) {
          header('WWW-Authenticate: Basic realm="NIGHTINGALE interface"');
          header('HTTP/1.0 401 Unauthorized');
          echo _('Access denied');
          exit();
      }
  }

  /**
   * @return Nightingale_Adapter_Interface
   */
  protected function _getAdapter() {
      if (!$this->_adapter) {
          $file = NIGHTINGALE_ROOT_PATH . DS . 'lib' . DS . 'adapters' . DS . DB_ADAPTER . '.php';
          if (file_exists($file)) {
              require_once $file;

              $class = 'NIGHTINGALE_Adapter_' . DB_ADAPTER;
              if (class_exists($class)) {
                  $adapter = new $class;
                  try {
                      $adapter->connect(DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD, DB_NAME);
                      $this->_adapter = $adapter;
                  } catch (Exception $e) {
                      $this->error("[{$e->getCode()}] " . $e->getMessage());
                  }
              }
          }
      }

      return $this->_adapter;
  }

  protected function error($message, $view = 'error_default')
  {
    header('HTTP/1.1 500 ' . $message);
    header('Content-Type: application/json; charset=UTF-8');
    $this->_view($view, ['message' => $message]);
    exit();
  }

  protected function success($view = 'success_default', $data = [])
  {
    $this->_view($view, $data);
    exit();
  }

  protected function _view($view, $data = [])
  {
    $file = NIGHTINGALE_ROOT_PATH . DS . 'templates' . DS . "$view.php";
    if (file_exists($file)) {
      extract($data);
      include($file);
    }
  }

  /**
    * SQL validation
    */

  protected function _validateSQL($sqlUp, $sqlDown)
  {
    $this->_getAdapter()->query("START TRANSACTION;");
    try {
      $this->_getAdapter()->query($sqlUp);
    }
    catch (\Exception $e)
    {
      $this->_getAdapter()->query("ROLLBACK;");
      header('HTTP/1.1 500 ' . $e->getMessage());
      header('Content-Type: application/json; charset=UTF-8');
      $this->error($e->getMessage(), 'mysql_error');
      die();
    }
    try {
      $this->_getAdapter()->query($sqlDown);
    }
    catch (\Exception $e)
    {
      $this->_getAdapter()->query("ROLLBACK;");
      header('HTTP/1.1 500 ' . $e->getMessage());
      header('Content-Type: application/json; charset=UTF-8');
      $this->error($e->getMessage(), 'mysql_error');
      die();
    }
    $this->_getAdapter()->query("ROLLBACK;");
  }

  /**
   * Singleton
   * @return Nightingale
   */
  static public function instance() {
      static $instance;
      $class = get_called_class();
      if (!($instance instanceof $class)) {
          $instance = new $class();
          $instance->git = Git::instance();
          if (!in_array(ENVIRONMENT, ENVIRONMENTS)) {
            $instance->_readonly = false;
          }

          $instance->_database = new NightingaleDatabase(ENVIRONMENT);
          $instance->_getAdapter();
      }

      return $instance;
  }

}
