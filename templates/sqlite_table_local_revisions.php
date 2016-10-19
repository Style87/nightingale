<div class="row">
  <div class="col-xs-12">
    <h2 class="inline-header">local_revisions</h2>
  </div>
</div>

<div class="row">
  <div class="col-xs-12">
    <table id="local-revisions-table" class="table table-striped table-hover">
      <thead>
        <tr>
          <th>revision_id</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($this->_database->select("SELECT * FROM local_revisions") as $migration) : ?>
          <tr data-id="<?= $migration['revision_id']; ?>">
            <td class="pointer"><?= $migration['revision_id']; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
$(document).ready(function() {
  $('#local-revisions-table').DataTable({
    "bLengthChange": false
  });
  $('#local-revisions-table tr').click(function(){
    window.location = '<?= BASE_URL; ?>/revisions/' + $(this).data('id');
  })
});
</script>
