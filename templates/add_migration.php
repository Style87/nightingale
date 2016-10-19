<?php $mMigration = NULL; ?>
<?php if (isset($this->id) && !empty($this->id)) : ?>
  <?php $mMigration = Migration::findFirst($this->_database, $this->id); ?>
<?php endif; ?>
<div class="well">
  <div class="form-group label-floating <?= $mMigration != NULL ? '' : 'is-empty' ?>">
    <label for="name" class="control-label">Name</label>
    <input type="text" class="form-control" id="name" value="<?= $mMigration != NULL ? $mMigration->getName() : '' ?>">
    <span class="help-block">A descriptive name for the migration sql.</span>
  </div>

  <div class="form-group">
    <label for="previous_migration_id" class="control-label">Preceding Migration</label>
    <select class="form-control" id="previous_migration_id">
      <option value="">No Preceding Migration</option>
      <?php $migrations = Migration::getPrecedingMigrations($this->_database); ?>
      <?php foreach ($migrations as $migration) : ?>
        <option <?= ($mMigration != NULL && $mMigration->getPreviousMigrationId() == $migration->getId()) ? 'selected' : '' ?> value="<?= $migration->getId() ?>">
          <?= $migration->getName() ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="form-group">
    <label for="previous_migration_id" class="control-label">Up SQL</label>
    <textarea class="form-control" rows="20" id="up-sql" required><?= $mMigration != NULL ? $mMigration->getUpSql() : '' ?></textarea>
  </div>

  <div class="form-group">
    <label for="previous_migration_id" class="control-label">Down SQL</label>
    <textarea class="form-control" rows="20" id="down-sql"><?= $mMigration != NULL ? $mMigration->getDownSql() : '' ?></textarea>
  </div>

  <div class="row">
    <div class="col-xs-12">
      <?php if ($mMigration == NULL) : ?>
        <input type="button" class="btn btn-primary pull-right btn-raised" value="Add" id="add-migration">
      <?php else : ?>
        <input type="button" class="btn btn-primary pull-right btn-raised" value="Edit" id="update-migration">
      <?php endif; ?>
    </div>
  </div>
</div>
<script type="text/javascript">
  $('#add-migration').on('click',function(){
    if (isFormValid())
    {
      return;
    }

    $this = $(this);
    $this.addClass('disabled');

    $.ajax({
      url : '<?= BASE_URL; ?>/migrations/createMigration',
      data : {
        name : $('#name').val(),
        up_sql : $('#up-sql').val(),
        down_sql : $('#down-sql').val(),
        previous_migration_id : $('#previous_migration_id option:selected').val()
      },
      success : function(data) {
        window.location = '<?= BASE_URL; ?>/migrations';
      },
      error : function(jqXHR, textStatus, errorThrown) {
        $this.removeClass('disabled');
        $('body').append(jqXHR.responseText);
      }
    });
  })
  $('#update-migration').on('click',function(){
    if (isFormValid())
    {
      return;
    }

    $this = $(this);
    $this.addClass('disabled');

    $.ajax({
      url : '<?= BASE_URL; ?>/migrations/<?= $this->id ?>/updateMigration',
      data : {
        name : $('#name').val(),
        up_sql : $('#up-sql').val(),
        down_sql : $('#down-sql').val(),
        previous_migration_id : $('#previous_migration_id option:selected').val()
      },
      success : function(data) {
        window.location = '<?= BASE_URL; ?>/migrations';
      },
      error : function(jqXHR, textStatus, errorThrown) {
        $this.removeClass('disabled');
        $('body').append(jqXHR.responseText);
      }
    });
  })
  function isFormValid()
  {
    var isError = false;
    $('.has-error').removeClass('has-error');
    if ($('#up-sql').val() == '')
    {
      isError = true;
      $('#up-sql').closest('.form-group').addClass('has-error');
    }
    if ($('#down-sql').val() == '')
    {
      isError = true;
      $('#down-sql').closest('.form-group').addClass('has-error');
    }
    if ($('#name').val() == '')
    {
      isError = true;
      $('#name').closest('.form-group').addClass('has-error');
    }
    return isError;
  }
</script>
