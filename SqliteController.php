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

class SqliteController extends Nightingale
{
  public function displayIndexAction()
  {
    $right = '';
    if ($this->id == 'migrations')
    {
      $right = 'sqlite_table_migrations';
    }
    else if ($this->id == 'local_revisions')
    {
      $right = 'sqlite_table_local_revisions';
    }
    else if ($this->id == 'local_revision_migrations')
    {
      $right = 'sqlite_table_local_revision_migrations';
    }
    $this->_view("index", [
      'top'  => 'sqlite_tables',
      //'right' => $right
    ]);
  }

  public function updateAction()
  {
    Logger::log(print_r($_POST['migrations'], true));
    foreach ($_POST['migrations'] as $migrationId => $hasRun)
    {
      $migration = Migration::findFirst($this->_database, $migrationId);
      $migration->setHasRun($hasRun);
      if (!$migration->save($this->_database))
      {
        $this->error("Error updating migration $migrationId");
      }
    }
  }
}
