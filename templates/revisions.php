<?php global $PERMISSIONS; ?>
<?php $revisions = Revision::factory($this->_database); ?>
<?php $localRevisions = LocalRevision::factory($this->_database); ?>
<?php $migrationCount = Migration::find($this->_database); ?>
<?php $currentrevision = Revision::getCurrentRevision(); ?>
<div id="left-container" class="well">
  <div id="left-header">
    <h2><?php echo __('Revisions'); ?></h2>

    <?php if ($PERMISSIONS[ENVIRONMENT]['ADD_REVISION']) : ?>
      <div class="row" style="margin-bottom:15px;">
        <div class="col-xs-12">
          <?php if ($migrationCount > 0) : ?>
            <a href="<?= BASE_URL ?>/revisions/displayAddRevision" class="btn btn-info btn-raised">Add Revision</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div id="left-body" class="row material-scrollbar">
    <table class="table table-condensed table-hover">
      <?php foreach ($localRevisions as $revision) : ?>
        <tr class="revision pointer" data-revision="<?= $revision->getId(); ?>">
          <td valign="middle">
            <h4><?= $revision->getId(); ?></h4>
          </td>
          <td valign="middle">
              <span class="label label-default pull-right">Pending</span>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php foreach ($revisions as $revision) : ?>
        <tr class="revision pointer" data-revision="<?= $revision->getId(); ?>">
          <td valign="middle">
            <h4><?= $revision->getId(); ?></h4>
          </td>
          <td valign="middle">
            <?php if ($revision->getId() != $currentrevision) : ?>
              <?php if ($PERMISSIONS[ENVIRONMENT]['RUN_REVISION']) : ?>
                <input type="button" class="btn btn-default btn-sm btn-raised pull-right migrate-to-revision" value="Migrate To" data-revision="<?= $revision->getId() ?>">
              <?php endif; ?>
            <?php endif; ?>
            <?php if($revision->getId() == $currentrevision) : ?>
              <span class="label label-info pull-right">Current</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (sizeof($revisions) == 0 && count($localRevisions) == 0) : ?>
        <tr><td>No revisions.</td></tr>
      <?php endif; ?>
    </table>
  </div>
</div>

<script type="text/javascript">
$('.revision').on('click',function(){
  window.location = '<?= BASE_URL ?>/revisions/'+$(this).data('revision');
});
$('.migrate-to-revision').on('click',function(e){
  e.preventDefault();
  e.stopPropagation();
  $this = $(this);
  $this.addClass('disabled').prop('disabled', true);
  $.ajax({
    url : '<?= BASE_URL; ?>/revisions/'+$(this).data('revision')+'/migrateToRevision',
    success : function(data) {
      console.log($(this));
      window.location = '<?= BASE_URL ?>/revisions/';
    },
    error : function(jqXHR, textStatus, errorThrown) {
      $this.removeClass('disabled').prop('disabled', false);
      $('body').append(jqXHR.responseText);
    }
  });
});

</script>
