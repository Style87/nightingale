<?php
class Migration
{
  protected $id;
  protected $previous_migration_id = '';
  protected $name;
  protected $has_run = 0;
  protected $up_sql;
  protected $down_sql;
  protected $revision_id = 0;

  protected $predecessorMigrations = NULL;

  function __construct($params = [])
  {
    if (isset($params['id']))
    {
      $this->setId($params['id']);
    }
    if (isset($params['previous_migration_id']))
    {
      $this->setPreviousMigrationId($params['previous_migration_id']);
    }
    if (isset($params['name']))
    {
      $this->setName($params['name']);
    }
    if (isset($params['has_run']))
    {
      $this->setHasRun($params['has_run']);
    }
    if (isset($params['up_sql']))
    {
      $this->setUpSql($params['up_sql']);
    }
    if (isset($params['down_sql']))
    {
      $this->setDownSql($params['down_sql']);
    }
    if (isset($params['revision_id']))
    {
      $this->setRevisionId($params['revision_id']);
    }
  }

  static function factory($migrationDataArray)
  {
    $migrations = [];
    foreach ($migrationDataArray as $migration)
    {
      $migrations[] = new Migration($migration);
    }
    return $migrations;
  }

  static function findFirst(&$source, $id = NULL)
  {
    if (is_string($source) && $id == NULL)
    {
      if (file_exists($source))
      {
        $result = (array)json_decode(file_get_contents($source));
      }
    }
    else
    {
      $result = $source->select("SELECT * FROM migrations WHERE id = '$id'");
      if (!empty($result) && count($result) == 1)
      {
        $result = $result[0];
      }
    }

    if (empty($result))
    {
      return false;
    }
    return new Migration($result);
  }

  static function find(&$db, $params = NULL)
  {
    if ($params != NULL)
    {
      $sql = "SELECT ";
      if (isset($params['columns']))
      {
        $sql .= "{$params['columns']} ";
      }
      else
      {
        $sql .= "* ";
      }
      $sql .= "FROM migrations ";
      if (isset($params['join']))
      {
        $sql .= "{$params['join']} ";
      }
      if (isset($params['where']))
      {
        $sql .= "WHERE {$params['where']} ";
      }
      $migrations = Migration::factory($db->select($sql));
    }
    else
    {
      $migrationsById = [];

      $dir = new DirectoryIterator(NIGHTINGALE_MIGRATIONS_PATH);
      foreach ($dir as $file) {
        if (!$file->isDot() && $file->getExtension() == 'migration') {
          $migration_id = str_replace('.' . $file->getExtension(), '', $file->getBasename());
            $migrationsById[$migration_id] = Migration::findFirst($db, $migration_id);
        }
      }

      $migrations = [];
      while (count($migrationsById) > 0)
      {
        foreach($migrationsById as $key => &$migration)
        {
          if ($migration->getPreviousMigrationId() == NULL || array_key_exists($migration->getPreviousMigrationId(), $migrations))
          {
            $migrations[$key] = $migration;
            unset($migrationsById[$key]);
          }
        }
      }
    }

    return $migrations;
  }

  static public function getMigrationsTree(&$db, $migrations = NULL)
  {
    if ($migrations == NULL)
    {
      $migrationsById = Migration::find($db);
    }

    foreach($migrationsById as $key => &$migration)
    {
      if ($migration->getPreviousMigrationId() != NULL)
      {
        $migration->previousMigration = $migrationsById[$migration->getPreviousMigrationId()];
      }
    }

    return $migrationsById;
  }

  public function json()
  {
    return json_encode([
      'id' => $this->getId(),
      'revision_id' => $this->getRevisionId(),
      'previous_migration_id' => $this->getPreviousMigrationId(),
      'name' => $this->getName(),
      'up_sql' => $this->getUpSql(),
      'down_sql' => $this->getDownSql()
    ]);
  }

  function save(&$db)
  {
    if (empty($this->getId()) || count($db->select("SELECT id FROM migrations WHERE id = '{$this->getId()}'")) == 0)
    {
      return $this->insert($db);
    }
    else
    {
      return $this->update($db);
    }
  }

  private function insert(&$db)
  {
    if (empty($this->id))
    {
      $this->id = NightingaleDatabase::GUIDv4();
    }

    $up_sql   = SQLite3::escapeString($this->up_sql);
    $down_sql = SQLite3::escapeString($this->down_sql);

    $db->execute("INSERT INTO migrations
      (id, revision_id, previous_migration_id, name, up_sql, down_sql)
      VALUES
      ('{$this->getId()}', {$this->getRevisionId()}, '{$this->getPreviousMigrationId()}', '{$this->getName()}', '$up_sql', '$down_sql')
    ");

    // Write out the new migration file
    $newMigrationFile = fopen($this->getFileName(true), "w");
    fwrite($newMigrationFile, $this->json());
    fclose($newMigrationFile);

    return true;
  }

  private function update(&$db)
  {
    $up_sql   = SQLite3::escapeString($this->up_sql);
    $down_sql = SQLite3::escapeString($this->down_sql);

    $db->execute("UPDATE migrations
      SET
          revision_id={$this->getRevisionId()}
        , previous_migration_id='{$this->getPreviousMigrationId()}'
        , name='{$this->getName()}'
        , up_sql='$up_sql'
        , down_sql='$down_sql'
        , has_run='{$this->getHasRun()}'
      WHERE id = '{$this->getId()}'
    ");

    // Update the file
    if (file_exists($this->getFileName(true)))
    {
      unlink($this->getFileName(true));
    }

    // Write out the new migration file
    $newMigrationFile = fopen($this->getFileName(true), "w");
    fwrite($newMigrationFile, $this->json());
    fclose($newMigrationFile);

    return true;
  }

  function delete(&$db = NULL, &$adapter)
  {
    if ($this->getHasRun() == 1)
    {
      $this->runDownMigration($db, $adapter);
    }

    if ($db != NULL)
    {
      $db->deleteMigration($this->getId());
    }

    $this->deleteFile();
  }

  function deleteFile()
  {
    if (file_exists($this->getFileName(true)))
    {
      unlink($this->getFileName(true));
    }
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

  function getPreviousMigrationId()
  {
    return $this->previous_migration_id;
  }

  function setPreviousMigrationId($previous_migration_id)
  {
    $this->previous_migration_id = $previous_migration_id;
    return $this;
  }

  function getName()
  {
    return $this->name;
  }

  function setName($name)
  {
    $this->name = $name;
    return $this;
  }

  function getHasRun()
  {
    return $this->has_run;
  }

  function setHasRun($has_run)
  {
    $this->has_run = $has_run;
    return $this;
  }

  function getUpSql()
  {
    return $this->up_sql;
  }

  function setUpSql($up_sql)
  {
    $this->up_sql = $up_sql;
    return $this;
  }

  function getDownSql()
  {
    return $this->down_sql;
  }

  function setDownSql($down_sql)
  {
    $this->down_sql = $down_sql;
    return $this;
  }

  function getRevisionId()
  {
    return $this->revision_id;
  }

  function setRevisionId($revision_id)
  {
    $this->revision_id = $revision_id;
    return $this;
  }

  function getDir()
  {
    if ($this->getRevisionId() > 0)
    {
      return NIGHTINGALE_REVISIONS_PATH . DS . $this->getRevisionId();
    }
    else
    {
      return NIGHTINGALE_MIGRATIONS_PATH;
    }
  }

  function getRelativeDir()
  {
    if ($this->getRevisionId() > 0)
    {
      return NIGHTINGALE_REVISIONS_PATH_RELATIVE . DS . $this->getRevisionId();
    }
    else
    {
      return NIGHTINGALE_MIGRATIONS_PATH_RELATIVE;
    }
  }

  public function getFileName($withPath = false)
  {
    return ($withPath ? $this->getDir() . DS : '') . $this->getId() . '.migration';
  }

  public function getRelativeFileName()
  {
    return $this->getRelativeDir() . DS . $this->getId() . '.migration';
  }

  public function runUpMigration(&$db, &$adapter)
  {
    if ($this->getHasRun() == 1) return true;

    $rMigrations = [];

    $rMigrations[] = $this;
    $migration = $this;
    while ($migration->getPreviousMigrationId() != NULL) {
      $migration = Migration::findFirst($db, $migration->getPreviousMigrationId());
      if ($migration->getHasRun() == 1)
      {
        break;
      }
      $rMigrations[] = $migration;
    }

    // Reverse the migrations
    $migrations = array_reverse($rMigrations);

    foreach ($migrations as $migration) {
      // TODO: Find a php lib to validate a sql string.
      if (empty($migration->getUpSql()))
      {
        continue;
      }
      $adapter->query(str_replace("\n", '', $migration->getUpSql()));
      $migration->setHasRun(1);
      $migration->save($db);
    }
  }

  public function runDownMigration(&$db, &$adapter)
  {
    if ($this->getHasRun() == 0) return;

    $migrations = [];

    $this->_runDownMigrationForPredecessors($db, $adapter, $this->getId());

    $adapter->query($this->getDownSql());
    $this->setHasRun(0);
    $this->save($db);
  }

  protected function _runDownMigrationForPredecessors(&$db, &$adapter, $migration_id)
  {
    $migrations = Migration::find($db, ['where' => "previous_migration_id = '$migration_id'"]);

    foreach($migrations as $migration)
    {
      $this->_runDownMigrationForPredecessors($db, $adapter, $migration->getId());

      // TODO: Find a php lib to validate a sql string.
      if (empty($migration->getDownSql()))
      {
        continue;
      }
      $adapter->query(str_replace("\n", '', $migration->getDownSql()));
      $migration->setHasRun(0);
      $migration->save($db);
    }
  }

  public function getPredecessors(&$db)
  {
    if ($this->predecessorMigrations == NULL)
    {
      $this->predecessorMigrations = Migration::find($db, ['where' => "previous_migration_id = '$this->id'"]);
    }

    return $this->predecessorMigrations;
  }

  static public function getOutstandingMigrations(&$db)
  {
    $result = $db->select("SELECT COUNT(*) as count FROM migrations WHERE revision_id = 0 AND has_run = 0");

    return $result[0]['count'];
  }

  static public function getPrecedingMigrations(&$db)
  {
    $allMigrations = Migration::find($db);
    $migrations = [];

    // TODO: Find a way to make this better than O(n^2)
    foreach($allMigrations as $aKey => $aMigration)
    {
      $hasRevert = false;
      foreach($allMigrations as $bKey => $bMigration)
      {
        if ($bMigration->getName() === 'Revert - ' . $aMigration->getName())
        {
          $hasRevert = true;
          break;
        }
      }
      if (!$hasRevert)
      {
        $migrations[] = $aMigration;
      }
    }

    return $migrations;
  }

}
