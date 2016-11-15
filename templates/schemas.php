<?php global $PERMISSIONS; ?>
<div id="left-container" class="well">
  <div id="left-header">
    <h2 class="inline"><?php echo __('Schema'); ?>
      <a href="#" id="import" class="btn btn-sm btn-info btn-raised no-margin pull-right">Import</a>
      <a href="#" id="export" class="btn btn-sm btn-info btn-raised no-margin pull-right" style="margin-right:5px;"  data-overwrite="0">Export</a>
    </h2>
  </div>

  <div id="left-body" class="row material-scrollbar">
    <div class="row no-margin">
      <div class="col-xs-12">
          <h4>
            <div class="checkbox no-margin inline">
              <label>
                <input type="checkbox" id="checkbox-master">
              </label>
            </div>
          </h4>
      </div>
    </div>
    <?php foreach ($dbSchema as $type => $schemas) : ?>
      <div class="row no-margin" >
        <div class="col-xs-1">
          <h2>
            <div class="checkbox no-margin inline">
              <label>
                <input type="checkbox" id="checkbox-<?= $type ?>" class="checkbox checkbox-type" data-type="<?= $type ?>" <?= (sizeof($schemas) == 0) ? 'disabled' : '' ?>>
              </label>
            </div>
          </h2>
        </div>
        <div class="col-xs-11" style="text-transform: uppercase;">
          <h2><?= $type; ?></h2>
        </div>
      </div>
      <?php if (sizeof($schemas) == 0) : ?>
        <div class="row no-margin">
          <div class="col-xs-12">
            No <?= $type; ?>.
          </div>
        </div>
      <?php endif; ?>
      <?php foreach ($schemas as $name => $schema) : ?>
        <div class="row no-margin" style="border-bottom:1px solid #ddd;">
          <div class="col-xs-1" style="text-align:middle;">
            <h4>
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="schemaObject" id="checkbox-<?= $type ?>-<?= $name ?>" class="checkbox checkbox-<?= $type ?> checkbox-schema" data-type="<?= $type; ?>" data-disk="<?= $schema->getOnDisk() ? '1' : '0' ?>" data-db="<?= $schema->getOnDB() ? '1' : '0' ?>" value="<?= $name ?>">
                </label>
              </div>
            </h4>
          </div>
          <div class="col-xs-8">
            <h4 style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis; word-break: break-all; word-wrap: break-word;"><?= $name ?></h4>
          </div>
          <div class="col-xs-3 text-right" style="text-transform: uppercase;">
            <span class="label label-<?= $schema->getOnDisk() ? 'success' : 'danger' ?> label-schema">Disk</span>
            <span class="label label-<?= $schema->getOnDB() ? 'success' : 'danger' ?> label-schema">DB</span>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </div>
</div>

<div class="modal" id="export-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Confirm schema overwrite</h4>
      </div>
      <div class="modal-body">
        <p>Exporting schema objects that are already on disk will overwrite current contents.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-raised" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary btn-raised export-modal-export" data-overwrite="0">Export</button>
        <button type="button" class="btn btn-danger btn-raised export-modal-export" data-overwrite="1">Export and Overwrite</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">

$('#checkbox-master').click(function(e){
  $('.checkbox'+':not(:disabled)').prop('checked', $(this).is(':checked'));
});

$('.checkbox-type').click(function(e){
  var type = $(this).data('type');
  $('.checkbox-'+type+':not(:disabled)').prop('checked', $(this).is(':checked'));
  $('#checkbox-master').prop('checked', $('.checkbox-schema:checked').length == $('.checkbox-schema').length);
});

$('.checkbox-schema').click(function(e){
  var type = $(this).data('type');
  $('#checkbox-'+type).prop('checked', $('.checkbox-'+type+':checked').length == $('.checkbox-'+type).length);
  $('#checkbox-master').prop('checked', $('.checkbox-schema:checked').length == $('.checkbox-schema').length);
});

$('#export').click(function(e){
  e.preventDefault();
  e.stopPropagation();
  if ($('.checkbox-schema[data-disk="1"]:checked').length > 0)
  {
    $('#export-modal').modal('show');
    return;
  }

  // Ajax export
  exportSchema($(this).data('overwrite'));
});

$('.export-modal-export').click(function(e){
  exportSchema($(this).data('overwrite'));
});

$('#import').click(function(e){
  e.preventDefault();
  e.stopPropagation();
  if ($('.checkbox-schema[data-db="1"]:checked').length > 0)
  {
    $('body').append('<div class="error-message alert alert-dismissible alert-warning"><button type="button" class="close" data-dismiss="alert">×</button>Schema objects on database will be skipped.</div>');
    $(".error-message").fadeTo(5000, 500).slideUp(500, function(){
        $(".error-message").alert('close');
    });
  }

  var schemaObjects = {};
  $('input:checkbox[name=schemaObject]:checked').each(function(){
    if (!($(this).data('type') in schemaObjects))
    {
      schemaObjects[$(this).data('type')] = [];
    }
    schemaObjects[$(this).data('type')].push($(this).val());
  });

  // Ajax import
  $.ajax({
    url : '<?= BASE_URL; ?>/schema/import',
    data : {
      schemaObjects : JSON.stringify(schemaObjects)
    },
    success : function(data) {
      window.location = '<?= BASE_URL; ?>/schema';
    },
    error : function(jqXHR, textStatus, errorThrown) {
      $('#add-schema').removeClass('disabled');
      $('body').append(jqXHR.responseText);
    }
  });
});


function exportSchema(overwrite)
{
  var schemaObjects = {};
  $('input:checkbox[name=schemaObject]:checked').each(function(){
    if (!($(this).data('type') in schemaObjects))
    {
      schemaObjects[$(this).data('type')] = [];
    }
    schemaObjects[$(this).data('type')].push($(this).val());
  });

  $.ajax({
    url : '<?= BASE_URL; ?>/schema/export',
    data : {
      schemaObjects : JSON.stringify(schemaObjects),
      overwrite : overwrite
    },
    success : function(data) {
      window.location = '<?= BASE_URL; ?>/schema';
    },
    error : function(jqXHR, textStatus, errorThrown) {
      $('#add-schema').removeClass('disabled');
      $('body').append(jqXHR.responseText);
    }
  });
}

</script>
