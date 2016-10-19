<?php $migration = Migration::findFirst($this->_database, $this->id); ?>

<?php $previous_migration = Migration::findFirst($this->_database, $migration->getPreviousMigrationId()); ?>
<?php $predecessorMigrations = $migration->getPredecessors($this->_database)?>

<div class="well">
  <div class="row">
    <div class="col-xs-12 col-md-4">
      <h4>Migration</h4>
      <label for="name">Name:</label> <?= $migration->getName(); ?><br>
    </div>
    <div class="col-xs-12 col-md-4">
      <h4>Preceding Migration</h4>
      <?php if ($previous_migration != NULL) : ?>
          <label for="name">Name:</label> <a href="<?= BASE_URL ?>/migrations/<?= $previous_migration->getId(); ?>"><?= $previous_migration->getName(); ?></a><br>
      <?php else : ?>
          No preceding migration
      <?php endif; ?>
    </div>
    <div class="col-xs-12 col-md-4">
      <h4>Predecessor Migrations</h4>
      <div class="single-line material-scrollbar">
        <?php foreach ($predecessorMigrations as $predecessorMigration) : ?>
            <label for="name">Name:</label> <a href="<?= BASE_URL ?>/migrations/<?= $predecessorMigration->getId(); ?>"><?= $predecessorMigration->getName(); ?></a><br>
        <?php endforeach; ?>
      </div>
      <?php if (count($predecessorMigrations) == 0) : ?>
          No preceding migration
      <?php endif; ?>
    </div>
  </div>

  <div class="row">
    <div class="col-xs-12">
      <h4>Migration SQL</h4>
      <div class="form-group">
        <label for="previous_migration_id">Up SQL</label>
        <pre><?= $migration->getUpSql(); ?></pre>
      </div>
      <div class="form-group">
        <label for="previous_migration_id">Down SQL</label>
        <pre><?= $migration->getDownSql(); ?></pre>
      </div>
    </div>
  </div>

    <div class="row">
      <div class="col-xs-12">
        <?php if ($this->git->isMigrationCommitted($migration)) : ?>
          <input type="button" class="btn btn-danger btn-raised" value="Revert" id="revert-migration" data-id="<?= $migration->getId() ?>" data-toggle="tooltip" data-placement="top" data-original-title="revert migration">
        <?php else : ?>
          <input type="button" class="btn btn-danger btn-raised <?= count($predecessorMigrations) > 0 ? 'disabled' : ''?>" value="Delete" id="delete-migration" data-id="<?= $migration->getId() ?>" data-toggle="tooltip" data-placement="top" data-original-title="<?= count($predecessorMigrations) > 0 ? 'cannot delete due to predecessors' : 'delete migration'?>">

          <?php
          $editTooltipText = '';
          if (count($predecessorMigrations) > 0 || $migration->getHasRun() == 1)
          {
            if (count($predecessorMigrations) > 0)
            {
              $editTooltipText = "cannot edit due to predecessors";
            }

            if ($migration->getHasRun() == 1)
            {
              $editTooltipText .= (strlen($editTooltipText) > 0 ? '<br>' : '' ) . 'cannot edit run migration';
            }
          }
          else
          {
            $editTooltipText = 'edit migration';
          }
          ?>
          <input type="button" class="btn btn-default btn-raised <?= ($migration->getHasRun() == 1 || count($predecessorMigrations) > 0) ? 'disabled' : '' ?>"  data-toggle="tooltip" data-placement="top" data-original-title="<?= $editTooltipText; ?>" data-html="true" value="Edit">
        <?php endif; ?>

        <?php if ($migration->getHasRun() == 0) : ?>
          <input type="button" class="btn btn-primary pull-right btn-raised" value="Run Up" id="run-up-migration" data-id="<?= $migration->getId() ?>" data-toggle="tooltip" data-placement="top" data-original-title="run up sql">
        <?php else : ?>
          <input type="button" class="btn btn-primary pull-right btn-raised" value="Run Down" id="run-down-migration" data-id="<?= $migration->getId() ?>" data-toggle="tooltip" data-placement="top" data-original-title="run down sql">
        <?php endif; ?>

        <?php if (!$this->git->isMigrationCommitted($migration)) : ?>
          <button class="btn btn-raised btn-default pull-right" id="push-migration" data-toggle="tooltip" data-placement="top" data-original-title="push migration">
            Push
          </button>
        <?php endif; ?>
      </div>
    </div>

</div>
<script type="text/javascript">
  $('#run-up-migration').on('click',function(e){
    e.preventDefault();
    e.stopPropagation();

    $this = $(this);

    if ($this.hasClass('disabled')) return false;

    $this.addClass('disabled').prop('disabled', true);
    $.ajax({
      url : '<?= BASE_URL; ?>/migrations/<?= $this->id; ?>/runUpMigration',
      success : function(data) {
        window.location = '<?= BASE_URL; ?>/migrations';
      },
      error : function(jqXHR, textStatus, errorThrown) {
        $this.removeClass('disabled').prop('disabled', false);
        $('body').append(jqXHR.responseText);
      }
    });
  });

  $('#run-down-migration').on('click',function(e){
    e.preventDefault();
    e.stopPropagation();

    $this = $(this);

    if ($this.hasClass('disabled')) return false;

    $this.addClass('disabled').prop('disabled', true);
    $.ajax({
      url : '<?= BASE_URL; ?>/migrations/<?= $this->id; ?>/runDownMigration',
      success : function(data) {
        window.location = '<?= BASE_URL; ?>/migrations';
      },
      error : function(jqXHR, textStatus, errorThrown) {
        $this.removeClass('disabled').prop('disabled', false);
        $('body').append(jqXHR.responseText);
      }
    });
  });

  $('#revert-migration').on('click',function(e){
    e.preventDefault();
    e.stopPropagation();

    $this = $(this);

    if ($this.hasClass('disabled')) return false;

    $this.addClass('disabled').prop('disabled', true);
    $.ajax({
      url : '<?= BASE_URL; ?>/migrations/<?= $this->id; ?>/revertMigration',
      success : function(data) {
        window.location = '<?= BASE_URL; ?>/migrations';
      },
      error : function(jqXHR, textStatus, errorThrown) {
        $this.removeClass('disabled').prop('disabled', false);
        $('body').append(jqXHR.responseText);
      }
    });
  });

  $('#delete-migration').on('click',function(e){
    e.preventDefault();
    e.stopPropagation();

    $this = $(this);

    if ($this.hasClass('disabled')) return false;

    $this.addClass('disabled').prop('disabled', true);
    $.ajax({
      url : '<?= BASE_URL; ?>/migrations/<?= $this->id; ?>/deleteMigration',
      success : function(data) {
        window.location = '<?= BASE_URL; ?>/migrations';
      },
      error : function(jqXHR, textStatus, errorThrown) {
        $this.removeClass('disabled').prop('disabled', false);
        $('body').append(jqXHR.responseText);
      }
    });
  });

  $('#push-migration').on('click',function(e){
    e.preventDefault();
    e.stopPropagation();

    $this = $(this);

    if ($this.hasClass('disabled')) return false;

    $this.addClass('disabled').prop('disabled', true);
    $.ajax({
      url : '<?= BASE_URL; ?>/migrations/<?= $this->id; ?>/pushMigration',
      success : function(data) {
        window.location = '<?= BASE_URL; ?>/migrations';
      },
      error : function(jqXHR, textStatus, errorThrown) {
        $this.removeClass('disabled').prop('disabled', false);
        $('body').append(jqXHR.responseText);
      }
    });
  });

  $('#edit-migration').click(function(e){
    e.preventDefault();
    e.stopPropagation();

    $this = $(this);

    if ($this.hasClass('disabled')) return false;

    window.location = '<?= BASE_URL ?>/migrations/<?= $migration->getId() ?>/displayUpdateMigration';
  })
</script>
