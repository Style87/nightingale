<div class="row">
  <div class="col-xs-12">
    <?php if (isset($this->id) && !empty($this->id)) : ?>
      <input type="button" class="btn btn-primary pull-right btn-raised" value="Edit revision" id="update-migration">
    <?php else : ?>
      <input type="button" class="btn btn-primary pull-right btn-raised" value="Create revision" id="add-migration">
    <?php endif; ?>
  </div>
</div>
<?php $selectedMigrations = Revision::findFirst($this->_database, $this->id)->getMigrations(); ?>
<?php $migrations = Migration::getMigrationsTree($this->_database); ?>
<?php foreach($migrations as $migration) : ?>
  <div class="panel panel-info">
    <div class="panel-heading">
      <h3 class="panel-title inline">
        <?= $migration->getName(); ?>
        <a name="<?= $migration->getId(); ?>"></a>
      </h3>
      <div class="checkbox pull-right inline panel-heading-checkbox ">
        <label>
          <input type="checkbox" id="migration-<?= $migration->getId() ?>" class="selected-migration" name="migrations[]" value="<?= $migration->getId() ?>" data-previous="<?= $migration->getPreviousMigrationId() ?>" <?= array_key_exists($migration->getId(), $selectedMigrations) ? 'checked' : ''?>>
        </label>
      </div>
    </div>
    <div class="panel-body">
      <div class="row">
        <div class="col-xs-12">
            <label>Previous Migration: </label> <a href="#<?= $migration->getPreviousMigrationId(); ?>"><?= isset($migration->previousMigration) ? $migration->previousMigration->getName() : ''; ?></a>
        </div>
      </div>

      <div class="row">
        <div class="col-xs-12 col-md-6">
            <label>Up Sql</label>
            <pre><code><?= $migration->getUpSql(); ?></code></pre>
        </div>
        <div class="col-xs-12 col-md-6">
            <label>Down Sql</label>
            <pre><code><?= $migration->getDownSql(); ?></code></pre>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<script type="text/javascript">
  // Properly select and deselect migration requirements
  $('.selected-migration').click(function(){
    if ($(this).is(':checked'))
    {
      var previous = $(this).data('previous');
      if (previous != '')
      {
        if (!$('#migration-' + previous).is(':checked'))
        {
          $('#migration-' + previous).click();
        }
      }
    }
    else
    {
      $(".selected-migration[data-previous='"+$(this).val()+"']").each(function(){
        if ($(this).is(':checked'))
        {
          $(this).click();
        }
      })
    }
  });

  $('#add-migration').on('click',function(){
    var migrations = [];
    $('.selected-migration:checked').each(function(){
      migrations.push($(this).val());
    })

    if (migrations.length == 0) {
      // Log an error
      return;
    }

    $.ajax({
      url : '<?= BASE_URL; ?>/revisions/createRevision',
      data : {
        migrations : JSON.stringify(migrations)
      },
      success : function(data) {
        window.location = '<?= BASE_URL; ?>/revisions';
      },
      error : function(jqXHR, textStatus, errorThrown) {
        $('body').append(jqXHR.responseText);
      }
    });
  })

  $('#update-migration').on('click',function(){
    var migrations = [];
    $('.selected-migration:checked').each(function(){
      migrations.push($(this).val());
    })

    if (migrations.length == 0) {
      // Log an error
      return;
    }

    $.ajax({
      url : '<?= BASE_URL; ?>/revisions/<?= $this->id; ?>/updateRevision',
      data : {
        migrations : JSON.stringify(migrations)
      },
      success : function(data) {
        window.location = '<?= BASE_URL; ?>/revisions/<?= $this->id; ?>';
      },
      error : function(jqXHR, textStatus, errorThrown) {
        $('body').append(jqXHR.responseText);
      }
    });
  })
</script>
