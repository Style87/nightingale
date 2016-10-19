<?php
class LocalRevision extends Revision
{
  protected $id;
  protected $migrations;

  static function factory(&$db)
  {
    $return = [];
    $revisions = $db->select("SELECT * FROM local_revisions");
    foreach($revisions as $revision)
    {
      $return[] = self::findFirst($db, $revision['revision_id']);
    }

    return $return;
  }

  static function findFirst(&$db, $revision_id)
  {
    $revision = $db->select("SELECT * FROM local_revisions WHERE revision_id = '$revision_id'");
    if ($revision == NULL)
    {
      return false;
    }

    $results = $db->select("SELECT migrations.* FROM migrations JOIN local_revision_migrations ON local_revision_migrations.migration_id = migrations.id WHERE local_revision_migrations.revision_id = '{$revision_id}'");

    $migrations = Migration::factory($results);

    return new LocalRevision([
      'id' => $revision_id,
      'migrations' => $migrations
    ]);
  }

  static function find(&$db, $migration_id)
  {
    $return = [];
    $revisionIds = $db->select("SELECT DISTINCT revision_id FROM local_revision_migrations WHERE migration_id = '$migration_id'");
    foreach ($revisionIds as $revisionId)
    {
      $return[] = LocalRevision::findFirst($db, $revisionId['revision_id']);
    }

    return $return;
  }

  function save(&$db)
  {
    if (empty($this->id))
    {
      $now = new \DateTime();
      $now->setTimezone(new \DateTimeZone(TIMEZONE));
      $this->id = $now->format('Y-m-d His');
    }
    $db->execute("INSERT OR IGNORE INTO local_revisions (revision_id) VALUES ('{$this->id}')");
    $db->execute("DELETE FROM local_revision_migrations WHERE revision_id  ='{$this->id}'");

    foreach ($this->getMigrations() as $migration)
    {
      $revisionIds = $db->execute("INSERT INTO local_revision_migrations (revision_id, migration_id) VALUES ('{$this->id}', '{$migration->getId()}')");
    }
  }

  function delete(&$db)
  {
    $db->execute("DELETE FROM local_revisions WHERE revision_id  ='{$this->id}'");
    $db->execute("DELETE FROM local_revision_migrations WHERE revision_id  ='{$this->id}'");
  }

  public function deleteMigration(&$db, $id)
  {
    $db->execute("DELETE FROM local_revision_migrations WHERE revision_id  ='{$this->id}' AND migration_id = '{$id}'");
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
}
