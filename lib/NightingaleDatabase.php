<?php
class NightingaleDatabase extends SQLite3
{
  protected $environment;

  function __construct($environment)
  {
    $this->environment = $environment;

    // Determine if the database requires initialization
    $initialize = false;

    if (!file_exists(NIGHTINGALE_SQLITE_PATH . DS . $this->environment . '.db')) {
      $initialize = true;
    }

    // Open the database
    $this->open(NIGHTINGALE_SQLITE_PATH . DS . $this->environment . '.db');

    // Initialize the database if required
    if ($initialize) {
      $this->initialize();
    }
  }

  private function initialize()
  {
    $sql = "CREATE TABLE IF NOT EXISTS `migrations` (
      id VARCHAR(255) PRIMARY KEY,
      previous_migration_id VARCHAR(255) NOT NULL DEFAULT '',
      name VARCHAR(255) NOT NULL,
      has_run TINYINT NOT NULL DEFAULT 0,
      up_sql TEXT NOT NULL,
      down_sql TEXT NOT NULL,
      revision_id INTEGER NOT NULL DEFAULT 0
    );";

    $ret = $this->exec($sql);

    if (!$ret) {
      error_log($this->lastErrorMsg());
      $this->close();
      unlink(NIGHTINGALE_SQLITE_PATH . DS . $this->environment . '.db');
      return;
    }

    // Initialize any existing migrations
    $dir = new DirectoryIterator(NIGHTINGALE_MIGRATIONS_PATH);
    foreach ($dir as $fileinfo) {
      if (!$fileinfo->isDot() && $fileinfo->getExtension() == 'migration') {

        $migration = new Migration((array)json_decode(file_get_contents(NIGHTINGALE_MIGRATIONS_PATH . DS . $fileinfo->getFilename())));
        $up_sql   = SQLite3::escapeString($migration->getUpSql());
        $down_sql = SQLite3::escapeString($migration->getDownSql());

        $this->execute("INSERT INTO migrations
          (id, revision_id, previous_migration_id, name, up_sql, down_sql)
          VALUES
          ('{$migration->getId()}', {$migration->getRevisionId()}, '{$migration->getPreviousMigrationId()}', '{$migration->getName()}', '{$up_sql}', '{$down_sql}')
        ");

      }
    }

    $revisions = Revision::factory($this, false, true);
    foreach ($revisions as $revision)
    {
      // Initialize any existing migrations
      $dir = new DirectoryIterator(NIGHTINGALE_REVISIONS_PATH . DS . $revision->getId());
      foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot() && $fileinfo->getExtension() == 'migration') {

          $migration = new Migration((array)json_decode(file_get_contents(NIGHTINGALE_REVISIONS_PATH . DS . $revision->getId() . DS . $fileinfo->getFilename())));
          $up_sql   = SQLite3::escapeString($migration->getUpSql());
          $down_sql = SQLite3::escapeString($migration->getDownSql());

          $this->execute("INSERT INTO migrations
            (id, revision_id, previous_migration_id, name, up_sql, down_sql)
            VALUES
            ('{$migration->getId()}', {$migration->getRevisionId()}, '{$migration->getPreviousMigrationId()}', '{$migration->getName()}', '{$up_sql}', '{$down_sql}')
          ");

        }
      }
    }

    $sql = "CREATE TABLE IF NOT EXISTS `local_revisions` (
      revision_id CHAR(20) PRIMARY KEY NOT NULL
    );";

    $ret = $this->exec($sql);

    if (!$ret) {
      error_log($this->lastErrorMsg());
      $this->close();
      unlink(NIGHTINGALE_SQLITE_PATH . DS . $this->environment . '.db');
      return;
    }

    $sql = "CREATE TABLE IF NOT EXISTS `local_revision_migrations` (
      migration_id CHAR(255) NOT NULL,
      revision_id CHAR(20) NOT NULL,
      PRIMARY KEY (migration_id, revision_id)
    );";

    $ret = $this->exec($sql);

    if (!$ret) {
      error_log($this->lastErrorMsg());
      $this->close();
      unlink(NIGHTINGALE_SQLITE_PATH . DS . $this->environment . '.db');
      return;
    }
  }

  public function select($sql)
  {
    $results = [];
    $query = $this->_query($sql);
    while ($result = $query->fetchArray(SQLITE3_ASSOC))
    {
      $results[] = $result;
    }
    return $query == NULL ? [] : $results;
  }

  private function _query($sql)
  {
    try
    {
      return $this->query($sql);
    }
    catch (Exception $e)
    {
      error_log($sql);
      error_log($e->getMessage());
      throw $e;
    }
  }

  public function execute($sql)
  {
    try {
      $this->exec($sql);
    } catch (\Exception $e) {
      error_log($sql);
      error_log($e->getMessage());
      throw $e;
    }
  }

  public function getMigration($id)
  {
    return $this->query("SELECT * FROM migrations WHERE id = '$id'")->fetchArray(SQLITE3_ASSOC);
  }

  public function insertMigration($name, $previous_migration_id, $up_sql, $down_sql, $revision_id = 0)
  {
    $id       = self::GUIDv4();
    $up_sql   = SQLite3::escapeString($up_sql);
    $down_sql = SQLite3::escapeString($down_sql);

    if ($previous_migration_id != NULL && !empty($previous_migration_id))
    {
      $previous_migration_id = "'$previous_migration_id'";
    }
    else
    {
      $previous_migration_id = "NULL";
    }

    $sql = "INSERT INTO migrations
      (id, revision_id, previous_migration_id, name, up_sql, down_sql)
      VALUES
      ('$id', $revision_id, $previous_migration_id, '$name', '$up_sql', '$down_sql')
    ";
    try {
      $this->exec($sql);
    } catch (Exception $e) {
      error_log($e->getMessage());
      error_log($sql);
      throw $e;
    }

    return $this->query("SELECT * FROM migrations WHERE id = '$id'")->fetchArray(SQLITE3_ASSOC);
  }

  public function updateMigration($id, $revision_id, $name, $previous_migration_id, $up_sql, $down_sql, $has_run)
  {
    $up_sql   = SQLite3::escapeString($up_sql);
    $down_sql = SQLite3::escapeString($down_sql);

    if ($previous_migration_id != NULL && !empty($previous_migration_id))
    {
      $previous_migration_id = "'$previous_migration_id'";
    }
    else
    {
      $previous_migration_id = "NULL";
    }

    // Migration has an id but isnt in the local db so insert
    if (count($this->query("SELECT id FROM migrations WHERE id = '$id'")->fetchArray(SQLITE3_ASSOC)) == 0)
    {
      $sql ="INSERT INTO migrations
        (id, revision_id, previous_migration_id, name, up_sql, down_sql)
        VALUES
        ('$id', $revision_id, $previous_migration_id, '$name', '$up_sql', '$down_sql')
      ";
      try {
        $this->exec($sql);
      } catch (Exception $e) {
        error_log($e->getMessage());
        error_log($sql);
        throw $e;
      }
    }
    else
    {
      $sql = "UPDATE migrations
        SET
            revision_id=$revision_id
          , previous_migration_id=$previous_migration_id
          , name='$name'
          , up_sql='$up_sql'
          , down_sql='$down_sql'
          , has_run='$has_run'
        WHERE id = '$id'
      ";
      try {
        $this->exec($sql);
      } catch (Exception $e) {
        error_log($e->getMessage());
        error_log($sql);
        throw $e;
      }
    }

    return $this->query("SELECT * FROM migrations WHERE id = '$id'")->fetchArray(SQLITE3_ASSOC);
  }

  public function deleteMigration($id)
  {
    $sql = "DELETE FROM WHERE id = '$id'";
    try {
      $this->exec($sql);
    } catch (Exception $e) {
      error_log($e->getMessage());
      error_log($sql);
      throw $e;
    }
  }

  /**
  * Returns a GUIDv4 string
  *
  * Uses the best cryptographically secure method
  * for all supported pltforms with fallback to an older,
  * less secure version.
  *
  * @param bool $trim
  * @return string
  */
  static public function GUIDv4 ($trim = true)
  {
      // Windows
      if (function_exists('com_create_guid') === true) {
          if ($trim === true)
              return trim(com_create_guid(), '{}');
          else
              return com_create_guid();
      }

      // OSX/Linux
      if (function_exists('openssl_random_pseudo_bytes') === true) {
          $data = openssl_random_pseudo_bytes(16);
          $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
          $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
          return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
      }

      // Fallback (PHP 4.2+)
      mt_srand((double)microtime() * 10000);
      $charid = strtolower(md5(uniqid(rand(), true)));
      $hyphen = chr(45);                  // "-"
      $lbrace = $trim ? "" : chr(123);    // "{"
      $rbrace = $trim ? "" : chr(125);    // "}"
      $guidv4 = $lbrace.
                substr($charid,  0,  8).$hyphen.
                substr($charid,  8,  4).$hyphen.
                substr($charid, 12,  4).$hyphen.
                substr($charid, 16,  4).$hyphen.
                substr($charid, 20, 12).
                $rbrace;
      return $guidv4;
  }
}
