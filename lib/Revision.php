<?php
class Revision
{
  protected $id;
  protected $migrations;

  function __construct($params = [])
  {
    if (isset($params['id']))
    {
      $this->setId($params['id']);
    }
    if (isset($params['migrations']))
    {
      $this->setMigrations($params['migrations']);
    }
  }

  static function factory(&$db)
  {
    $return = [];

    foreach (new DirectoryIterator(NIGHTINGALE_REVISIONS_PATH) as $file) {
        if ($file->isDir() && !$file->isDot()) {
            $return[$file->getBasename()] = Revision::findFirst($db, $file->getBasename());
        }
    }

    krsort($return, SORT_NUMERIC);

    return $return;
  }

  static function findFirst(&$db, $id)
  {
    $migrations = [];

    $dir = new DirectoryIterator(NIGHTINGALE_REVISIONS_PATH . DS . $id);
    foreach ($dir as $file) {
      if (!$file->isDot() && $file->getExtension() == 'migration') {
        $migration_id = str_replace('.migration', '', $file->getBasename());
        $migrations[$migration_id] = Migration::findFirst($db, $migration_id);
        if ($migrations[$migration_id] === false)
        {
          $filename = NIGHTINGALE_REVISIONS_PATH . DS . $id . DS . $file->getBasename();
          $migrations[$migration_id] = Migration::findFirst($filename);
        }
      }
    }

    return new Revision([
      'id' => $id,
      'migrations' => $migrations
    ]);
  }

  public function json()
  {
    $migrationsJson = [];
    foreach ($this->migrations as $migration)
    {
      $migrationsJson[] = $migration->json();
    }
    return json_encode([
      'id' => $this->id,
      'migrations' => $migrationsJson
    ]);
  }

  function save(&$db)
  {
    $this->id = self::getCount()+1;
    if (file_exists(NIGHTINGALE_REVISIONS_PATH . DS . $this->id))
    {
      error_log("Error saving revision: Revision directory already exists.");
      return false;
    }
    // Create new revision folder
    if (!@mkdir($this->getDirName(), 0770))
    {
      error_log(error_get_last());
      return false;
    }

    // Copy migration files to new version folder
    foreach($this->migrations as $migration)
    {
      $migration->deleteFile();
      $migration->setRevisionId($this->id);
      $migration->save($db);
    }

    return true;
  }

  function getId()
  {
    return $this->id;
  }

  function setId($id)
  {
    $this->id = $id;
    return $this;
  }

  function getMigrations()
  {
    return $this->migrations;
  }

  function setMigrations($migrations)
  {
    $this->migrations = $migrations;
    return $this;
  }

  static public function getCount()
  {
    $count = 0;
    foreach (new DirectoryIterator(NIGHTINGALE_REVISIONS_PATH) as $file)
    {
        if ($file->isDir() && !$file->isDot() && is_numeric($file->getBasename()))
        {
            $count++;
        }
    }
    return $count;
  }

  static public function getCurrentRevision()
  {
    $file = NIGHTINGALE_META_PATH . DS . 'revision';
    if (file_exists($file)) {
        return intval(file_get_contents($file));
    }
    return 0;
  }

  static public function setCurrentRevision($revision)
  {
    $file = NIGHTINGALE_META_PATH . DS . 'revision';
    if (!file_exists($file))
    {
      touch($file);
    }
    if (!file_put_contents($file, $revision))
    {
      Logger::log(error_get_last());
      return false;
    }
    return true;
  }

  public function getDirName()
  {
    return NIGHTINGALE_REVISIONS_PATH . DS . $this->getId();
  }

  public function getRelativeDirName()
  {
    return NIGHTINGALE_REVISIONS_PATH_RELATIVE . DS . $this->getId();
  }
}
