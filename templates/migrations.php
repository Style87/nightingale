<?php global $PERMISSIONS; ?>
<div id="left-container" class="well">
  <div id="left-header">
    <h2><?php echo __('Migrations'); ?></h2>

    <div class="row" style="margin-bottom:15px;">
      <div class="col-xs-12">
        <?php if ($PERMISSIONS[ENVIRONMENT]['ADD_MIGRATION']) : ?>
          <a href="<?= BASE_URL ?>/migrations/displayAddmigration" class="btn btn-info btn-raised">Add Migration</a>
        <?php endif; ?>
        <?php if ($PERMISSIONS[ENVIRONMENT]['RUN_MIGRATION']) : ?>
          <input type="button" class="btn btn-default pull-right btn-raised" value="Run All" id="run-all-migration">
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?= $this->git->getStatus(); ?>
  <?php $this->git->getUntrackedFiles(); ?>

  <?php $migrations = Migration::find($this->_database); ?>
  <div id="left-body" class="row material-scrollbar">
    <table class="table table-condensed table-hover">
      <tbody>
      <?php foreach ($migrations as $migration) : ?>
        <tr class="migration pointer">
          <td data-id="<?= $migration->getId() ?>" class="text-middle"><h4><?= $migration->getName() ?></h4></td>
          <td valign="middle" align="right" data-id="<?= $migration->getId() ?>">
            <?php if ($PERMISSIONS[ENVIRONMENT]['RUN_MIGRATION']) : ?>
              <?php if ($migration->getHasRun() == 0) : ?>
                <button class="btn btn-sm migrations-run-migration-up btn-raised btn-info" data-id="<?= $migration->getId() ?>" data-toggle="tooltip" data-placement="top" title="run up sql">
                  Run up
                </button>
              <?php else : ?>
                <button class="btn btn-sm migrations-run-migration-down btn-danger btn-raised" data-id="<?= $migration->getId() ?>" data-toggle="tooltip" data-placement="top" title="run down sql">
                  Run down
                </button>
              <?php endif; ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script type="text/javascript">
  $('.migration td').on('click',function(){
    window.location = '<?= BASE_URL ?>/migrations/'+$(this).data('id');
  });

  $('.migrations-run-migration-up').on('click',function(e){
    e.preventDefault();
    e.stopPropagation();
    $this = $(this);
    $this.addClass('disabled').prop('disabled', true);
    $.ajax({
      url : '<?= BASE_URL; ?>/migrations/' + $(this).data('id') + '/runUpMigration',
      error : function(jqXHR, textStatus, errorThrown) {
        $this.removeClass('disabled').prop('disabled', false);
        $('body').append(jqXHR.responseText);
      }
    });
  });

  $('.migrations-run-migration-down').on('click',function(e){
    e.preventDefault();
    e.stopPropagation();
    $this = $(this);
    $this.addClass('disabled').prop('disabled', true);
    $.ajax({
      url : '<?= BASE_URL; ?>/migrations/' + $(this).data('id') + '/runDownMigration',
      error : function(jqXHR, textStatus, errorThrown) {
        $this.removeClass('disabled').prop('disabled', false);
        $('body').append(jqXHR.responseText);
      }
    });
  });

  $('#run-all-migration').on('click',function(e){
    e.preventDefault();
    e.stopPropagation();
    $this = $(this);
    $this.addClass('disabled').prop('disabled', true);
    $.ajax({
      url : '<?= BASE_URL; ?>/migrations/runAllMigrations',
      error : function(jqXHR, textStatus, errorThrown) {
        $this.removeClass('disabled').prop('disabled', false);
        $('body').append(jqXHR.responseText);
      }
    });
  });
</script>
