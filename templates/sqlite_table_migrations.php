<div class="row">
  <div class="col-xs-12">
    <h2 class="inline-header">migrations</h2>
    <input type="button" id="btn-save" class="btn btn-primary btn-raised pull-right disabled" disabled value="save">
    <input type="button" class="btn btn-danger btn-raised pull-right" value="Reload" data-toggle="tooltip" data-placement="bottom" title="reload the local database from the migration files">
  </div>
</div>

  <div class="row">
    <div class="col-xs-12">
      <table id="migrations-table" class="table table-striped table-hover">
        <thead>
          <tr>
            <th>id</th>
            <th>previous migration id</th>
            <th>revision id</th>
            <th>name</th>
            <th>has run</th>
            <th>up sql</th>
            <th>down sql</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($this->_database->select("SELECT * FROM migrations") as $migration) : ?>
            <tr>
              <td class="migration pointer" data-id="<?= $migration['id']; ?>"><?= $migration['id']; ?></td>
              <td class="previous-migration pointer" data-id="<?= $migration['previous_migration_id']; ?>"><?= $migration['previous_migration_id']; ?></td>
              <td><?= $migration['revision_id']; ?></td>
              <td><?= $migration['name']; ?></td>
              <td>
                <div class="checkbox pull-right inline panel-heading-checkbox ">
                  <label>
                    <input type="checkbox" class="has-run" value="1" data-id="<?= $migration['id']; ?>" data-original="<?= $migration['has_run'] ?>" <?= $migration['has_run'] == 1 ? 'checked' : ''; ?>>
                  </label>
                </div>
              </td>
              <td><pre><?= $migration['up_sql']; ?></pre></td>
              <td><pre><?= $migration['down_sql']; ?></pre></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<script type="text/javascript">
  $(document).ready(function() {
    $('#migrations-table').DataTable({
      "bLengthChange": false,
      "order": [[ 2, "desc" ], [ 3, "asc"]]
    });

    $('#migrations-table tr td.previous-migration').click(function(){
      if ($(this).data('id') == '') return;
      window.location = '<?= BASE_URL; ?>/revisions/' + $(this).data('id');
    });

    $('#migrations-table tr td.migration').click(function(){
      window.location = '<?= BASE_URL; ?>/migrations/' + $(this).data('id');
    });

  });
  $('.has-run').on('click',function(){
    $('#btn-save').addClass('disabled').prop('disabled', true);
    $('.has-run').each(function(){
      if (($(this).data('original') == 1 && !$(this).is(":checked")) || ($(this).data('original') == 0 && $(this).is(":checked")))
      {
        $('#btn-save').removeClass('disabled').prop('disabled', false);
      }
    })
  });

  $('#btn-save').click(function(e){
    e.preventDefault();
    e.stopPropagation();
    var migrations = {};
    $('.has-run').each(function(){
      if (($(this).data('original') == 1 && !$(this).is(":checked")) || ($(this).data('original') == 0 && $(this).is(":checked")))
      {
        // Add to list
        migrations[$(this).data('id')] = $(this).is(":checked") ? 1 : 0;
      }
    })

    $.ajax({
      type : 'POST',
      url : '<?= BASE_URL; ?>/sqlite/update',
      data : {
        'migrations' : migrations
      },
      success : function(data) {
        window.location.reload();
      },
      error : function(jqXHR, textStatus, errorThrown) {
        $('body').append(jqXHR.responseText);
      }
    });
  });

</script>
