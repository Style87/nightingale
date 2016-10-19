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

class RevisionsController extends Nightingale
{
  public function displayIndexAction()
  {
    $revision = NULL;
    if ($this->id != NULL)
    {
      if (is_numeric($this->id))
      {
        $revision = Revision::findFirst($this->_database, $this->id);
      }
      else
      {
        $revision = LocalRevision::findFirst($this->_database, $this->id);
      }
    }
    $this->_view("index", [
      'left'  => 'revisions',
      'right' => $this->id == NULL ? '' : 'revision',
      'revision' => $revision
    ]);
  }

  public function displayAddRevisionAction()
  {
    $this->_view("index", [
      'left'  => 'revisions',
      'right' => 'add_revision'
    ]);
  }

  public function displayUpdateRevisionAction()
  {
    $this->_view("index", [
      'left'  => 'revisions',
      'right' => 'add_revision'
    ]);
  }

  public function createRevisionAction()
  {
    // Decode the migrations
    $migrationIds = json_decode($_GET['migrations']);
    $migrations = [];

    foreach ($migrationIds as $migrationId)
    {
      $migrations[] = Migration::findFirst($this->_database, $migrationId);
    }

    $revision = new LocalRevision([
      'migrations' => $migrations
    ]);

    $revision->save($this->_database);
  }

  public function readRevisionAction()
  {
    $revision = Revision::findFirst($this->_database, $this->id);
    $this->_view("index", [
      'left'     => 'revisions',
      'right'    => 'revision',
      'revision' => $revision
    ]);
  }

  public function readLocalRevisionAction()
  {
    $revision = LocalRevision::findFirst($this->_database, $this->id);
    $this->_view("index", [
      'left'     => 'revisions',
      'right'    => 'revision',
      'revision' => $revision
    ]);
  }

  public function updateRevisionAction()
  {
    // Decode the migrations
    $migrationIds = json_decode($_GET['migrations']);
    $migrations = [];

    foreach ($migrationIds as $migrationId)
    {
      $migrations[] = Migration::findFirst($this->_database, $migrationId);
    }

    $revision = Revision::findFirst($this->_database, $this->id);
    $revision->setMigrations($migrations);

    $revision->save();
  }

  public function deleteRevisionAction()
  {
    $revision = Revision::findFirst($this->_database, $this->id);
    $revision->delete();
  }

  public function pushRevisionAction()
  {
    // Ensure git is in sync
    if (!$this->git->isUpToDate())
    {
      $this->error('Git is out of date. Please update and try again.', 'error_git');
    }

    $localRevision = LocalRevision::findFirst($this->_database, $this->id);
    $revision = new Revision([
      'migrations' => $localRevision->getMigrations()
    ]);

    // Save the new revision
    if (!$revision->save($this->_database))
    {
      $this->error(error_get_last());
    }

    // Delete the local revision
    $localRevision->delete($this->_database);

    // Push the new revision
    $this->git->pushRevision($this->_database, $revision);
  }

  public function migrateToRevisionAction() {
    // Decode the migrations
    $targetRevision = $this->id;
    $currentRevision = Revision::getCurrentRevision();

    $this->_getAdapter()->query("START TRANSACTION;");
    try {
      // Migrate up
      if ($targetRevision > $currentRevision)
      {
        Logger::log('Migrate up');
        for($currentRevision++;$currentRevision<=$targetRevision;$currentRevision++)
        {
          $revision = Revision::findFirst($this->_database, $currentRevision);
          foreach($revision->getMigrations() as $migration)
          {
            $migration->runUpMigration($this->_database, $this->_adapter);
          }
        }

        if (!Revision::setCurrentRevision($targetRevision))
        {
          $this->error("Cannot write revision file");
        }
      }
      // Migrate down
      else if ($targetRevision < $currentRevision)
      {
        Logger::log('Migrate down');
        // Migrate down all outstanding revisions
        $migrations = Migration::find($this->_database);
        foreach($migrations as $migration)
        {
          $migration->runDownMigration($this->_database, $this->_adapter);
        }

        // Migrate down all versions to the target version
        for($currentRevision;$currentRevision>$targetRevision;$currentRevision--)
        {
          $revision = Revision::findFirst($this->_database, $currentRevision);
          foreach($revision->getMigrations() as $migration)
          {
            $migration->runDownMigration($this->_database, $this->_adapter);
          }
        }
        if (!Revision::setCurrentRevision($targetRevision))
        {
          $this->error("Cannot write revision file");
        }
      }
    } catch (Exception $e) {
      Logger::log($e->getMessage());
      $this->_getAdapter()->query("ROLLBACK;");
    }
    $this->_getAdapter()->query("COMMIT;");
  }

}
