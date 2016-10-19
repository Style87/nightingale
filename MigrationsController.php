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

class MigrationsController extends Nightingale
{
  public function displayIndexAction()
  {
    $this->_view("index", [
      'left'  => 'migrations',
      'right' => $this->id == NULL ? '' : 'migration'
    ]);
  }

  public function displayAddMigrationAction()
  {
    $this->_view("index", [
      'left'  => 'migrations',
      'right' => 'add_migration'
    ]);
  }

  public function displayUpdateMigrationAction()
  {
    $this->_view("index", [
      'left'  => 'migrations',
      'right' => 'add_migration'
    ]);
  }

  public function createMigrationAction()
  {
    // Ensure git is in sync
    if (!$this->git->isUpToDate())
    {
      $this->error('Git is out of date.', 'error_git');
    }

    // Validate sql
    $this->_validateSQL($_GET['up_sql'], $_GET['down_sql']);

    $migration = new Migration($_GET);
    $migration->save($this->_database);

    // Write out the new migration file
    $newMigrationFile = fopen(NIGHTINGALE_MIGRATIONS_PATH . DS . "{$migration->getId()}.migration", "w");
    fwrite($newMigrationFile, $migration->json());
    fclose($newMigrationFile);
  }

  public function readMigrationAction()
  {
    $this->_view("index", [
      'left'  => 'migrations',
      'right' => 'migration'
    ]);
  }

  public function updateMigrationAction()
  {
    // Ensure git is in sync
    if (!$this->git->isUpToDate())
    {
      $this->error('Git is out of date.', 'error_git');
    }

    // Validate sql
    $this->_validateSQL($_GET['up_sql'], $_GET['down_sql']);

    $migration = Migration::findFirst($this->_database, $this->id);
    $migration
      ->setName($_GET['name'])
      ->setPreviousMigrationId(isset($_GET['previous_migration_id']) ? $_GET['previous_migration_id'] : NULL)
      ->setUpSql($_GET['up_sql'])
      ->setDownSql($_GET['down_sql'])
      ->save($this->_database);
  }

  public function revertMigrationAction()
  {
    // Ensure git is in sync
    if (!$this->git->isUpToDate())
    {
      $this->git->pull();
    }

    $targetMigration = Migration::findFirst($this->_database, $this->id);

    $migrations = $this->_getMigrations();

    foreach ($migrations as $migration)
    {
      if ($migration->getName() == 'Revert - ' . $targetMigration->getName())
      {
        $this->error('Migration already reverted.');
      }
    }

    $newMigration = new Migration();
    $newMigration
      ->setName('Revert - ' . $targetMigration->getName())
      ->setPreviousMigrationId($targetMigration->getId())
      ->setHasRun(0)
      ->setUpSql($targetMigration->getDownSql())
      ->setDownSql($targetMigration->getUpSql())
      ->save($this->_database);
  }

  public function deleteMigrationAction()
  {
    $targetMigration = Migration::findFirst($this->_database, $this->id);
    $targetMigration->delete($this->_database, $this->_adapter);
  }

  public function pushMigrationAction()
  {
    // Ensure git is in sync
    if (!$this->git->isUpToDate())
    {
      $this->error('Git is out of date. Please update and try again.', 'error_git');
    }

    $this->git->pushMigration($this->_database, Migration::findFirst($this->_database, $this->id));
  }

  public function runAllMigrationsAction() {
    $migrations = Migration::find($this->_database);

    foreach($migrations as $migration)
    {
      try
      {
        $this->_getAdapter()->query("START TRANSACTION;");
        $migration->runUpMigration($this->_database, $this->_adapter);
        $this->_getAdapter()->query("COMMIT;");
      }
      catch (Exception $e)
      {
        $this->_getAdapter()->query("ROLLBACK;");
        $this->error($e->getMessage());
      }
    }
  }

  public function runUpMigrationAction()
  {
    $this->_getAdapter()->query("START TRANSACTION;");

    try
    {
      $migration = Migration::findFirst($this->_database, $this->id);
      $migration->runUpMigration($this->_database, $this->_adapter);
    } catch (Exception $e) {
      $this->_getAdapter()->query("ROLLBACK;");
      $this->error($e->getMessage());
    }
    $this->_getAdapter()->query("COMMIT;");
  }

  public function runDownMigrationAction()
  {
    $this->_getAdapter()->query("START TRANSACTION;");

    try
    {
      $migration = Migration::findFirst($this->_database, $this->id);
      $migration->runDownMigration($this->_database, $this->_adapter);
    } catch (Exception $e) {
      $this->_getAdapter()->query("ROLLBACK;");
      $this->error($e->getMessage());
    }
    $this->_getAdapter()->query("COMMIT;");
  }

}
