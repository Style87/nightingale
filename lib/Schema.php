<?php
class Schema
{
  const EXTENSION = 'sql';
  const TYPE = '';
  protected $name;
  protected $sql;
  protected $onDisk = false;
  protected $onDB = false;

  static function factory()
  {
    $class = get_called_class();
    $return = [];
    $dir = new DirectoryIterator($class::getDir());
    foreach ($dir as $file) {
      if (!$file->isDot() && $file->getExtension() == self::EXTENSION) {
        $schema = new $class();
        $schema->setName(str_replace('.' . $file->getExtension(), '', $file->getBasename()));
        $schema->setSql(file_get_contents($file->getPathname()));
        $schema->setOnDisk(true);

        $return[$schema->name] = $schema;
      }
    }

    return $return;
  }

  static function findFirst($name)
  {
    $class = get_called_class();
    $file = $class::getDir() . DS . $name . '.' . self::EXTENSION;
    if (!file_exists($file))
    {
      return false;
    }
    $schema = new $class();
    $schema->setName($name);
    $schema->setSql(file_get_contents($file));
    $schema->setOnDisk(true);

    return $schema;
  }

  static function find()
  {
    $class = get_called_class();
    return $class::factory();
  }

  function save($overwrite = false)
  {
    if (file_exists($this->getFileName(true)) && !$overwrite)
    {
      return;
    }
    // Write out the new migration file
    $newSchemaFile = fopen($this->getFileName(true), "w");
    fwrite($newSchemaFile, $this->getSql());
    fclose($newSchemaFile);
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

  function getSql()
  {
    return $this->sql;
  }

  function setSql($sql)
  {
    $this->sql = $sql;
    return $this;
  }

  function getOnDisk()
  {
    return $this->onDisk;
  }

  function setOnDisk($onDisk)
  {
    $this->onDisk = $onDisk;
    return $this;
  }

  function getOnDB()
  {
    return $this->onDB;
  }

  function setOnDB($onDB)
  {
    $this->onDB = $onDB;
    return $this;
  }

  static public function schemaExists($name = 'initial')
  {
    $class = get_called_class();
    return is_file(NIGHTINGALE_SCHEMA_PATH . DS . $class::TYPE . $name . '.' . self::EXTENSION);
  }

  static public function getDir()
  {
    $class = get_called_class();
    return NIGHTINGALE_SCHEMA_PATH . ($class::TYPE != '' ? DS : '') . $class::TYPE;
  }

  static public function getRelativeDir()
  {
    $class = get_called_class();
    return NIGHTINGALE_SCHEMA_PATH_RELATIVE . ($class::TYPE != '' ? DS : '') . $class::TYPE;
  }

  public function getFileName($withPath = false)
  {
    $class = get_called_class();
    return ($withPath ? $class::getDir() . DS : '') . $this->getName() . '.' . self::EXTENSION;
  }

  public function getRelativeFileName()
  {
    $class = get_called_class();
    return $class::getRelativeDir() . DS . $this->getName() . '.' . self::EXTENSION;
  }

  public function getRevision()
  {
    $file = self::getDir() . DS . revision . '.schema';
    if (!file_exists($file))
    {
      $newSchemaRevisionFile = fopen($file, "w");
      fwrite($newSchemaRevisionFile, '');
      fclose($newSchemaRevisionFile);
    }
    return file_get_contents($file);
  }

  public function setRevision($revision)
  {
    $file = self::getDir() . DS . revision . '.schema';
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

  static public function getSchema(&$adapter)
  {
    $dbSchema = $adapter->getSchema();
    $diskSchema = self::getDiskSchema();

    // Convert db schema to objects
    foreach ($dbSchema as $type => $set)
    {
      $class = "Schema" . substr(ucfirst($type), 0, strlen($type)-1);
      foreach ($set as $key => $sql)
      {
        if (!isset($diskSchema[$type][$key]))
        {
          $mSchema = new $class();
          $mSchema->setOnDB(true);
          $mSchema->setSql($sql);
          $mSchema->setName($key);

          $diskSchema[$type][$key] = $mSchema;
        }
        else
        {
          $diskSchema[$type][$key]->setOnDB(true);
        }

      }
    }

    return $diskSchema;
  }

  static public function getDiskSchema()
  {
    return [
      'tables' => SchemaTable::find(),
      'views' => SchemaView::find(),
      'triggers' => SchemaTrigger::find(),
      'procedures' => SchemaProcedure::find(),
      'functions' => SchemaFunction::find(),
    ];
  }
}
