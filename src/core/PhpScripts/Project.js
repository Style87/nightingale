let script = `<?php
class Project
{
  protected $version;
  protected $maxVersion;
  protected $versions;
  protected $migrations;
  protected $_adapter;

  /**
   * Singleton
   * @return Nightingale
   */
  static public function instance() {
      static $instance;
      $class = get_called_class();
      if (!($instance instanceof $class)) {
          $instance = new $class();
          $project = json_decode(file_get_contents(NIGHTINGALE_DB_FILE));

          $instance->setVersionNumber($project->version);
          $instance->setMaxVersionNumber($project->maxVersion);
          
          foreach ($project->migrations as $migration) {
            $instance->setMigration(new Migration($migration));
          }
          foreach ($project->versions as $version) {
            $instance->setVersion(new Version($version));
          }

          $instance->_getAdapter();
      }

      return $instance;
  }

  public function toArray()
  {
    $project = [
      'version' => $this->getVersionNumber(),
      'maxVersion' => $this->getMaxVersionNumber(),
      'migrations' => [],
      'versions' => []
      
    ];
    $migrations = [];
    foreach ($this->migrations as $migration)
    {
      $migrations[$migration->getId()] = $migration->toArray();
    }
    $versions = [];
    foreach ($this->versions as $version)
    {
      $versions[$version->getId()] = $version->toArray();
    }
    $project['migrations'] = $migrations;
    $project['versions'] = $versions;
    
    return $project;
  }

  public function json()
  {
    return json_encode($this->toArray());
  }

  function save()
  {
    $handle = fopen(NIGHTINGALE_DB_FILE, 'w') or die('Cannot open file:  '.NIGHTINGALE_DB_FILE);
    fwrite($handle, $this->json());
    fclose($handle);

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

  function getMigration($key, $version = null)
  {
    $migration = null;
    if (array_key_exists($key, $this->migrations)) {
      $migration = $this->migrations[$key];
    }
    else if (isset($version)) {
      $migration = $this->getVersion($version)->getMigration($key);
    }
    else {
      foreach ($this->getVersions() as $version) {
        if ($version->getMigration($key) !== null) {
          $migration = $version->getMigration($key);
          break;
        }
      }
    }

    return $migration;
  }

  function setMigration($migration, $version = null)
  {
    if (isset($version)) {
      $this->getVersion($version)->setMigration($migration);
    }
    else {
      $this->migrations[$migration->getId()] = $migration;
    }
    return $this;
  }

  function getVersions()
  {
    return $this->versions;
  }

  function setVersions($versions)
  {
    $this->versions = $versions;
    return $this;
  }

  function getVersion($index)
  {
    return $this->versions[$index];
  }

  function setVersion($version)
  {
    $this->versions[$version->getId()] = $version;
    return $this;
  }

  function getVersionNumber()
  {
    return $this->version;
  }

  function setVersionNumber($versionNumber)
  {
    $this->version = $versionNumber;
    return $this;
  }

  function getMaxVersionNumber()
  {
    return $this->maxVersion;
  }

  function setMaxVersionNumber($versionNumber)
  {
    $this->maxVersion = $versionNumber;
    return $this;
  }
  
  public function _getAdapter() {
    if (!$this->_adapter) {
      $adapter = new Adapter_MySQL();
      try {
        $adapter->connect(DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD, DB_NAME);
        $this->_adapter = $adapter;
      } catch (Exception $e) {
        //Logger::log($e->getMessage());

        //$this->error("[{$e->getCode()}] " . $e->getMessage());

      }
    }

    return $this->_adapter;
  }
  
  public function migrateTo($toVersion)
  {
    $projectVersion = $this->getVersionNumber();
    
    // Migrate up
    if ($projectVersion <= $toVersion) {
        $projectVersion++;
        while ($projectVersion <= $toVersion) {
          $version = $this->getVersion($projectVersion);
          $version->migrateUp();
          $this->setVersionNumber($projectVersion++);
          $this->save();
        }
      }
      // Migrate down
      else {
        // Migrate down versions
        while ($projectVersion > $toVersion) {
          $version = $this->getVersion($projectVersion);
          $version->migrateDown();
          $this->setVersionNumber($projectVersion--);
          $this->save();
        }
      }
  }
  
  public function migrateToMaxVersion()
  {
    $this->migrateTo($this->getMaxVersionNumber());
  }
}
`;

export { script };