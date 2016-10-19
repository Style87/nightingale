<?php
class Logger
{
  static public function log($msg, $level = 'DEBUG')
  {
    $bt = debug_backtrace();
    $caller = array_shift($bt);
    error_log("{$caller['file']}({$caller['line']}): $msg");
  }
}
