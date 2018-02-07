<?php
class Version
{
  protected $id;
  protected $migrations;

  function __construct($params = [])
  {
    $params = (array) $params;
    if (isset($params['id']))
    {
      $this->setId($params['id']);
    }
    if (isset($params['migrations']))
    {
      foreach ($params['migrations'] as $migration) {
        $migration = (array) $migration;
        $this->setMigration(new Migration($migration));
      }
    }
  }

  static function factory()
  {
    $project = Project::instance();
    return $project->getVersions();
  }

  static function findFirst($id)
  {
    $project = Project::instance();
    return $project->getVersion($id);
  }

  public function toArray()
  {
    $version = [
      'id' => $this->getId(),
      'migrations' => []
    ];
    $migrations = [];
    foreach ($this->getMigrations() as $migration)
    {
      $migrations[$migration->getId()] = $migration->toArray();
    }
    $version['migrations'] = $migrations;

    return $version;
  }

  public function json()
  {
    return json_encode($this->toArray());
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

  public function getMigration($key)
  {
    $migration = null;
    if (array_key_exists($key, $this->getMigrations())) {
      $migration = $this->getMigrations()[$key];
    }
    return $migration;
  }
  
  public function setMigration($migration)
  {
    $this->migrations[$migration->getId()] = $migration;
    return $this;
  }
  
  public function migrateUp()
  {
    foreach ($this->getMigrations() as $migration) {
      $migration->runUpMigration();
    }
  }
  
  public function migrateDown()
  {
    foreach ($this->getMigrations() as $migration) {
      $migration->runDownMigration();
    }
  }
}
