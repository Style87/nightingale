<?php $migrationsTree = $revision->getMigrations(); ?>
<?php $currentrevision = Revision::getCurrentRevision(); ?>

<div class="row">
  <div class="col-xs-12">
    <h2 class="inline-header">Revision <?= $this->id ?></h2>
    <?php if (is_numeric($this->id) && $revision->getId() != $currentrevision) : ?>
      <input type="button" class="btn btn-default btn-sm btn-raised pull-right" id="migrate-to-revision" value="Migrate To" data-revision="<?= $this->id ?>">
    <?php elseif (!is_numeric($this->id)) : ?>

      <input type="button" id="push-revision" class="btn btn-default btn-sm btn-raised pull-right" value="Push" data-revision="<?= $this->id ?>">

      <a href="<?= BASE_URL ?>/revisions/<?= $this->id ?>/displayUpdateRevision" class="btn btn-primary btn-sm btn-raised pull-right">Edit</a>
      <input type="button" class="btn btn-danger btn-sm btn-raised pull-right" value="Delete" data-toggle="modal" data-target="#delete-confirmation-modal">
    <?php endif; ?>
  </div>
</div>
<?php foreach ($migrationsTree as $migration) : ?>
  <div class="panel panel-info">
    <div class="panel-heading">
      <h3 class="panel-title"><?= $migration->getName(); ?></h3>
    </div>
    <div class="panel-body">
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

<div class="modal fade" id="delete-confirmation-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><strong>Confirm revision deletion</strong></h4>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this revision?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-danger" id="delete-revision" data-revision="<?= $this->id ?>">Delete</button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  $('#delete-revision').on('click',function(e){
    e.preventDefault();
    e.stopPropagation();
    var $this = $(this);
    $this.addClass('disabled').prop('disabled', true);
    $.ajax({
      url : '<?= BASE_URL; ?>/revisions/<?= $this->id; ?>/deleteRevision',
      success : function(data) {
        window.location = '<?= BASE_URL; ?>/revisions/';
      },
      error : function(jqXHR, textStatus, errorThrown) {
        $this.removeClass('disabled').prop('disabled', false);
        $('body').append(jqXHR.responseText);
      }
    });
  });
  $('#push-revision').on('click',function(e){
    e.preventDefault();
    e.stopPropagation();
    var $this = $(this);
    $this.addClass('disabled').prop('disabled', true);
    $.ajax({
      url : '<?= BASE_URL; ?>/revisions/<?= $this->id; ?>/pushRevision',
      success : function(data) {
        window.location = '<?= BASE_URL; ?>/revisions/';
      },
      error : function(jqXHR, textStatus, errorThrown) {
        $this.removeClass('disabled').prop('disabled', false);
        $('body').append(jqXHR.responseText);
      }
    });
  });

  $('#migrate-to-revision').on('click',function(e){
    e.preventDefault();
    e.stopPropagation();
    $this = $(this);
    $this.addClass('disabled').prop('disabled', true);
    $.ajax({
      url : '<?= BASE_URL; ?>/revisions/<?= $this->id; ?>/migrateToRevision',
      success : function(data) {
        window.location.reload();
      },
      error : function(jqXHR, textStatus, errorThrown) {
        $this.removeClass('disabled').prop('disabled', false);
        $('body').append(jqXHR.responseText);
      }
    });
  });
</script>
