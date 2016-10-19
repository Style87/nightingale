<?php global $PERMISSIONS; ?>
<div class="well">
  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#migrations" aria-controls="migrations" role="tab" data-toggle="tab">migrations</a></li>
    <li role="presentation"><a href="#local_revisions" aria-controls="local_revisions" role="tab" data-toggle="tab">local_revisions</a></li>
    <li role="presentation"><a href="#local_revision_migrations" aria-controls="local_revision_migrations" role="tab" data-toggle="tab">local_revision_migrations</a></li>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane fade in active" id="migrations"><?php $this->_view('sqlite_table_migrations'); ?></div>
    <div role="tabpanel" class="tab-pane fade" id="local_revisions"><?php $this->_view('sqlite_table_local_revisions'); ?></div>
    <div role="tabpanel" class="tab-pane fade" id="local_revision_migrations"><?php $this->_view('sqlite_table_local_revision_migrations'); ?></div>
  </div>

</div>
<script>
// Javascript to enable link to tab
var url = document.location.toString();
if (url.match('#')) {
  $('.nav-tabs a[href="#' + url.split('#')[1] + '"]').tab('show');
}

// Change hash for page-reload
$('.nav-tabs a').on('shown.bs.tab', function (e) {
  window.location.hash = e.target.hash;
})
</script>
