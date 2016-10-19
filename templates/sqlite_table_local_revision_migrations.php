<div class="row">
  <div class="col-xs-12">
    <h2 class="inline-header">local_revision_migrations</h2>
  </div>
</div>

<div class="row">
  <div class="col-xs-12">
    <table id="local-revision-migrations-table" class="table table-striped table-hover">
      <thead>
        <tr>
          <th>revision_id</th>
          <th>migration_id</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($this->_database->select("SELECT * FROM local_revision_migrations") as $migration) : ?>
          <tr>
            <td class="revision pointer" data-id="<?= $migration['revision_id']; ?>"><?= $migration['revision_id']; ?></td>
            <td class="migration pointer" data-id="<?= $migration['migration_id']; ?>"><?= $migration['migration_id']; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
$(document).ready(function() {
  $('#local-revision-migrations-table').DataTable({
    "bLengthChange": false,
    "order": [[ 0, "desc" ], [ 1, "asc"]]
  });

  $('#local-revision-migrations-table tr td.revision').click(function(){
    window.location = '<?= BASE_URL; ?>/revisions/' + $(this).data('id');
  });

  $('#local-revision-migrations-table tr td.migration').click(function(){
    window.location = '<?= BASE_URL; ?>/migrations/' + $(this).data('id');
  });

});
</script>
