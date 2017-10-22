let script = `<?php

<?php

if (PHP_SAPI !== 'cli') {
  exit('Command line only execution allowed.');
}

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '/config.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '/Adapter_MySQL.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '/Project.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '/Version.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '/Migration.php';

define('DS', DIRECTORY_SEPARATOR);
define('NIGHTINGALE_PATH', '.' . DS . 'nightingale' . DS);
define('NIGHTINGALE_META_PATH', NIGHTINGALE_PATH . 'meta' . DS);
define('NIGHTINGALE_VERSIONS_PATH', NIGHTINGALE_PATH . 'versions' . DS);
define('NIGHTINGALE_MIGRATIONS_PATH', NIGHTINGALE_PATH . 'migrations' . DS);

define('NIGHTINGALE_DB_FILE', NIGHTINGALE_META_PATH . 'db.json');

$project = Project::instance();

// Save new versions to db.json and update max version
foreach (new DirectoryIterator(NIGHTINGALE_VERSIONS_PATH) as $folder) {
  if ($folder->isDir() && !$folder->isDot()) {
    $versionNumber = (int) $folder->getBasename();
    if ($versionNumber > $project->getMaxVersionNumber()) {
      
      $newVersion = new Version([
        'id' => $versionNumber,
        'migrations' => []
      ]);
      $dir = new DirectoryIterator(NIGHTINGALE_VERSIONS_PATH . $versionNumber);
      foreach ($dir as $file) {
        if (!$file->isDot() && $file->getExtension() == 'json') {
          $migration_id = str_replace('.json', '', $file->getBasename());
          $migration = json_decode(file_get_contents(NIGHTINGALE_VERSIONS_PATH . $versionNumber . DS . $file->getBasename()));
          $newVersion->setMigration(new Migration($migration));
        }
      }
      $project->setVersion($newVersion);
      $project->setMaxVersionNumber($versionNumber);
    }
    
  }
}

// Save new unversioned migrations to db.json
foreach (new DirectoryIterator(NIGHTINGALE_MIGRATIONS_PATH) as $file) {
  if (!$file->isDot() && $file->getExtension() == 'json') {
    $migration_id = str_replace('.json', '', $file->getBasename());
    if ($project->getMigration($migration_id) === null) {
      $migration = json_decode(file_get_contents(NIGHTINGALE_MIGRATIONS_PATH . $file->getBasename()));
      $project->setMigration(new Migration($migration));
    }
  }
}

echo print_r($project->toArray());
$project->save();

// Migrate down unversioned migrations not on disk
foreach ($project->getMigrations() as $migration) {
  if (!file_exists(NIGHTINGALE_MIGRATIONS_PATH . "{$migration->getId()}.json")) {
    $migration->runDownMigration();
  }
}
// Migrate to max version
$project->migrateToMaxVersion();

// Migrate up unversioned migrations on disk
foreach ($project->getMigrations() as $migration) {
  if (file_exists(NIGHTINGALE_MIGRATIONS_PATH . "{$migration->getId()}.json")) {
    $migration->runUpMigration();
  }
}

$project->save();
`;

export { script };