let script = `<?php
class Migration
{
  protected $id;
  protected $parentMigrationId = null;
  protected $description;
  protected $hasRun = false;
  protected $sqlUp;
  protected $sqlDown;
  protected $version = null;

  protected $predecessorMigrations = NULL;

  function __construct($params = [])
  {
    $params = (array) $params;
    if (isset($params['id']))
    {
      $this->setId($params['id']);
    }
    if (isset($params['parentMigrationId']))
    {
      $this->setParentMigrationId($params['parentMigrationId']);
    }
    if (isset($params['description']))
    {
      $this->setDescription($params['description']);
    }
    if (isset($params['hasRun']))
    {
      $this->setHasRun($params['hasRun']);
    }
    if (isset($params['sqlUp']))
    {
      $this->setSqlUp($params['sqlUp']);
    }
    if (isset($params['sqlDown']))
    {
      $this->setSqlDown($params['sqlDown']);
    }
    if (isset($params['version']))
    {
      $this->setVersion($params['version']);
    }
  }

  static function factory($migrationDataArray)
  {
    $migrations = [];
    foreach ($migrationDataArray as $migration)
    {
      $migrations[$migration['id']] = new Migration($migration);
    }
    return $migrations;
  }

  static function findFirst($id, $version = null)
  {
    $project = Project::instance();
    return $project->getMigration($id, $version);
  }

  public function toArray()
  {
    return [
      'id' => $this->getId(),
      'version' => $this->getVersion(),
      'parentMigrationId' => $this->getParentMigrationId(),
      'description' => $this->getDescription(),
      'sqlUp' => $this->getSqlUp(),
      'sqlDown' => $this->getSqlDown(),
      'hasRun' => $this->getHasRun()
    ];
  }

  public function json()
  {
    return json_encode($htis->toArray());
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

  function getParentMigrationId()
  {
    return $this->parentMigrationId;
  }

  function setParentMigrationId($parentMigrationId)
  {
    $this->parentMigrationId = $parentMigrationId;
    return $this;
  }

  function getDescription()
  {
    return $this->description;
  }

  function setDescription($description)
  {
    $this->description = $description;
    return $this;
  }

  function getHasRun()
  {
    return $this->hasRun;
  }

  function setHasRun($hasRun)
  {
    $this->hasRun = $hasRun;
    return $this;
  }

  function getSqlUp()
  {
    return $this->sqlUp;
  }

  function setSqlUp($sqlUp)
  {
    $this->sqlUp = $sqlUp;
    return $this;
  }

  function getSqlDown()
  {
    return $this->sqlDown;
  }

  function setSqlDown($sqlDown)
  {
    $this->sqlDown = $sqlDown;
    return $this;
  }

  function getVersion()
  {
    return $this->version;
  }

  function setVersion($version)
  {
    $this->version = $version;
    return $this;
  }

  public function runUpMigration()
  {
    if ($this->getHasRun()) {
      return true;
    }

    $project = Project::instance();

    $rMigrations = [];

    $rMigrations[] = $this;
    $migration = $this;
    while ($migration->getParentMigrationId() != NULL) {
      $migration = Migration::findFirst($migration->getParentMigrationId());
      if ($migration->getHasRun() == 1) {
        break;
      }
      $rMigrations[] = $migration;
    }

    // Reverse the migrations
    $migrations = array_reverse($rMigrations);

    foreach ($migrations as $migration) {
      // TODO: Find a php lib to validate a sql string.
      if (empty($migration->getSqlUp())) {
        continue;
      }
      $project->_getAdapter()->query(str_replace("\n", '', $migration->getSqlUp()));
      $migration->setHasRun(true);
    }
  }

  public function runDownMigration()
  {
    if (!$this->getHasRun()) {
      return;
    }

    $project = Project::instance();

    $this->_runDownMigrationForPredecessors();

    $project->_getAdapter()->query($this->getSqlDown());
    $this->setHasRun(false);
  }

  protected function _runDownMigrationForPredecessors()
  {
    foreach($this->getPredecessors() as $migration) {
      $migration->runDownMigration();
    }
  }

  public function getPredecessors()
  {
    $project = Project::instance();
    $migrations = [];
    if (isset($this->version)) {
      foreach ($project->getVersion($this->version)->getMigrations() as $migation) {
        if ($migration->getParentMigrationId() == $this->getId()) {
          $migrations[] = $migration;
        }
      }
    }
    else {
      foreach ($project->getMigrations() as $migration) {
        if ($migration->getParentMigrationId() == $this->getId()) {
          $migrations[] = $migration;
        }
      }
    }

    return $migrations;
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
`;

export { script };