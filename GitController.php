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

class GitController extends Nightingale
{
  public function readPullCountAction()
  {
    echo $this->git->getPullCount();
  }

  public function readPushCountAction()
  {
    echo count($this->git->getUntrackedFiles());
  }

  public function pullAction()
  {
    $this->git->pull($this->_database);
  }

  public function pushAction()
  {
    // Ensure git is in sync
    if (!$this->git->isUpToDate())
    {
      $this->error('Git is out of date. Please update and try again.', 'error_git');
    }
    $migrations = Migration::find($this->_database);

    foreach($migrations as $migration)
    {
      $this->git->pushMigration($this->_database, $migration);
    }

    $diskSchema = Schema::factory();
    foreach($diskSchema as $schema)
    {
      $this->git->pushSchemaObject($this->_database, $schema);
    }
  }
}
