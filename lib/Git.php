<?php
class Git {
  public $untrackedFiles = NULL;
  public $status = NULL;
  public $hasFetched = false;

  public function isUpToDate()
  {
    $status = $this->getStatus();
    return ($status == "Up-to-date" || $status == "Need to push");
  }

  public function getStatus()
  {
    if ($this->status == NULL)
    {
      shell_exec('git fetch');

      if ( (int)trim(shell_exec('git rev-list ' . NIGHTINGALE_GIT_BRANCH . '..' . NIGHTINGALE_GIT_REMOTE . '/' . NIGHTINGALE_GIT_BRANCH . ' --count')) > 0 )
      {
        return 'Need to pull';
      }
      else if ( count($this->getUntrackedMigrations()) > 0 )
      {
        return 'Need to push';
      }
      else
      {
        return 'Up-to-date';
      }
    }
    return $this->status;
  }

  public function getUntrackedFiles($includeRevisions = false)
  {
    if ($this->untrackedFiles == NULL)
    {
      $migrations = trim(shell_exec('git ls-files --others --exclude-standard'));
      if (empty($migrations))
      {
        return [];
      }
      $allFiles = explode("\n", str_replace('/', DS, $migrations));

      if (!$includeRevisions)
      {
        foreach ($allFiles as $key=>$file)
        {
          if (strpos($file, 'revisions') !== false)
          {
            unset($allFiles[$key]);
          }
        }
      }


      $this->untrackedFiles = $allFiles;
    }

    return $this->untrackedFiles;
  }

  public function getUntrackedMigrations()
  {
    $migrations = trim(shell_exec('git ls-files --others --exclude-standard'));
    if (empty($migrations))
    {
      return [];
    }
    $allFiles = explode("\n", str_replace('/', DS, $migrations));
    foreach ($allFiles as $key=>$file)
    {
      if (strpos($file, 'revisions') !== false || empty($file))
      {
        unset($allFiles[$key]);
      }
    }
    return $allFiles;
  }

  public function isMigrationCommitted($migration)
  {
    return !in_array($migration->getRelativeDir() . DS . $migration->getId() . ".migration", $this->getUntrackedFiles());
  }

  public function isRevisionMigrationCommitted($revision, $migration)
  {
    return !in_array($revision->getRelativeDirName() . DS . $migration->getFileName(), $this->getUntrackedFiles(true));
  }

  public function isSchemaObjectCommitted($schema)
  {
    return !in_array($schema->getRelativeDir() . DS . $schema->getFileName(), $this->getUntrackedFiles());
  }

  public function pushMigration(&$db, $migration = NULL)
  {
    if ($migration == NULL)
    {
      return false;
    }

    if (!$this->addMigration($db, $migration))
    {
      return false;
    }

    shell_exec('git commit -m "Commit migration '.$migration->getId().' and predecessors."');
    $this->pull($db);
    $this->push();

    return true;
  }

  protected function addMigration(&$db, $migration = NULL)
  {
    if ($migration == NULL || $this->isMigrationCommitted($migration))
    {
      return false;
    }

    shell_exec('git add ' . $migration->getFileName(true));
    if ($migration->getPreviousMigrationId() != NULL)
    {
      $this->addMigration($db, Migration::findFirst($db, $migration->getPreviousMigrationId()));
    }
    return true;
  }

  public function pushRevision(&$db, $revision)
  {
    foreach ($revision->getMigrations() as $migration)
    {
      $this->addRevisionMigration($db, $revision, $migration);
    }

    shell_exec('git commit -m "Commit revision '.$revision->getId().'."');
    $this->push();

    // Remove any migrations in local revisions that have been added to the pushed revision
    $localRevisions = LocalRevision::factory($db);
    foreach ($localRevisions as $localRevision)
    {
      $migrations = [];
      foreach ($localRevision->getMigrations() as $migration)
      {
        // Explicitly check the migrations path
        //  TODO: Faster to check if $migration->getId() key exists in $revision->getMigrations()?
        if (!file_exists(NIGHTINGALE_MIGRATIONS_PATH . DS . $migration->getFileName()))
        {
          $localRevision->deleteMigration($db, $migration->getId());
        }
      }
    }
  }

  protected function addRevisionMigration(&$db, $revision = NULL, $migration = NULL)
  {
    if ($revision == NULL || $migration == NULL || $this->isRevisionMigrationCommitted($revision, $migration))
    {
      return false;
    }

    // Remove previous migration id from any migrations that are predecessors not in the revision
    $predecessors = $migration->getPredecessors($db);
    foreach ($predecessors as $predecessorMigration)
    {
      // Skip predecessor migrations that are in the revision
      if (array_key_exists($predecessorMigration->getId(), $revision->getMigrations()))
      {
        continue;
      }
      $predecessorMigration->setPreviousMigrationId("");
      $predecessorMigration->save($db);
      shell_exec('git add ' . $predecessorMigration->getFileName(true));
    }

    shell_exec('git rm ' . NIGHTINGALE_MIGRATIONS_PATH . DS . $migration->getFileName());
    shell_exec('git add ' . $migration->getFileName(true));
  }

  public function pushSchemaObject(&$db, $schema = NULL)
  {
    if ($schema == NULL)
    {
      return false;
    }

    if (!$this->addSchemaObject($schema))
    {
      return false;
    }

    shell_exec('git commit -m "Commit schema object '.$schema->getName().'."');
    $this->push();

    return true;
  }

  protected function addSchemaObject($schema = NULL)
  {
    if ($schema == NULL || $this->isSchemaObjectCommitted($schema))
    {
      return false;
    }

    shell_exec('git add ' . $schema->getFileName(true));

    return true;
  }

  public function getPullCount()
  {
    $this->fetch();

    return trim(shell_exec('git rev-list ' . NIGHTINGALE_GIT_BRANCH . '..' . NIGHTINGALE_GIT_REMOTE . '/' . NIGHTINGALE_GIT_BRANCH . ' --count'));
  }

  public function getPushCount()
  {
    $this->fetch();

    $local = count(explode("\n", trim(shell_exec('git rev-list ' . NIGHTINGALE_GIT_BRANCH))));
    $remote = count(explode("\n", trim(shell_exec('git rev-list ' . NIGHTINGALE_GIT_REMOTE . '/' . NIGHTINGALE_GIT_BRANCH))));

    return max(0, $local - $remote);
  }

  public function fetch()
  {
    if (!$this->hasFetched)
    {
      shell_exec('git fetch');
      $this->hasFetched = true;
    }
  }

  public function pull(&$db)
  {
    // Get current max revision
    $maxRevision = Revision::getCount();
    Logger::log("current max revision: $maxRevision");
    $this->fetch();
    shell_exec("git pull");

    $newMaxRevision = Revision::getCount();
    Logger::log("new max revision: $newMaxRevision");
    for ($r=$maxRevision+1;$r<=$newMaxRevision;$r++)
    {
      $revision = Revision::findFirst($db, $r);

      foreach ($revision->getMigrations() as $key => $rMigration)
      {
        // Manually build filename because the revision isn't correct in the local db
        $filename = NIGHTINGALE_REVISIONS_PATH . DS . $revision->getId() . DS . $rMigration->getFileName();
        $migration = Migration::findFirst($filename);
        $migration->setHasRun($rMigration->getHasRun());
        $migration->save($db);

        $predecessors = $migration->getPredecessors($db);
        foreach ($predecessors as $predecessorMigration)
        {
          // Skip predecessor migrations that are in the revision
          if (array_key_exists($predecessorMigration->getId(), $revision->getMigrations()))
          {
            continue;
          }
          $predecessorMigration->setPreviousMigrationId("");
          $predecessorMigration->save($db);
        }
      }
    }

    // Remove any migrations in local revisions that have been added to a pushed revision
    $localRevisions = LocalRevision::factory($db);
    foreach ($localRevisions as $localRevision)
    {
      $migrations = [];
      foreach ($localRevision->getMigrations() as $migration)
      {
        // Explicitly check the migrations path
        if (!file_exists(NIGHTINGALE_MIGRATIONS_PATH . DS . $migration->getFileName()))
        {
          $localRevision->deleteMigration($db, $migration->getId());
        }
      }
    }

    // Initialize any new migrations
    $dir = new DirectoryIterator(NIGHTINGALE_MIGRATIONS_PATH);
    foreach ($dir as $fileinfo) {
      if (!$fileinfo->isDot() && $fileinfo->getExtension() == 'migration') {
        $migration_id = str_replace('.' . $fileinfo->getExtension(), '', $fileinfo->getBasename());
        if (Migration::findFirst($db, $migration_id) === false)
        {
          $migration = new Migration((array)json_decode(file_get_contents(NIGHTINGALE_MIGRATIONS_PATH . DS . $fileinfo->getFilename())));
          $migration->save($db);
        }
      }
    }
  }

  public function push()
  {
    shell_exec('git push ' . NIGHTINGALE_GIT_REMOTE . ' ' . NIGHTINGALE_GIT_BRANCH);
  }

  public function clean()
  {
    return;
    shell_exec('git reset --hard');
    shell_exec('git clean -df --exclude');
  }

  /**
   * Singleton
   * @return Git
   */
  static public function instance() {
      static $instance;
      $class = get_called_class();
      if (!($instance instanceof $class)) {
          $instance = new $class();
      }

      return $instance;
  }

  private function __construct()
  {

  }

}
