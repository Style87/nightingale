<div class="row">
	<div class="col-xs-12 col-md-4">
		<div class="well text-center">
			<h2><?php echo __('Revision'); ?></h2>
			<h3><?= Revision::getCurrentRevision(); ?></h3>
		</div>
	</div>
	<div class="col-xs-12 col-md-4">
		<div class="well text-center">
			<h2><?php echo __('Max Revision'); ?></h2>
			<h3>
				<?= Revision::getCount(); ?>
			</h3>
		</div>
	</div>
	<div class="col-xs-12 col-md-4">
		<div class="well text-center">
			<h2><?php echo __('Migrations'); ?></h2>
			<h3><?= Migration::getOutstandingMigrations($this->_database); ?></h3>
		</div>
	</div>
</div>
<div class="well">
  <div class="row">
    <div class="col-xs-12">
      <p><strong>Revision:</strong> A database has revisions that are sequential integers made up of migrations.</p>
    </div>
  </div>
  <div class="row">
    <div class="col-xs-12">
      <p><strong>Migration:</strong> A migration happens within a revision, may follow another migration, has a name and is made up of migration SQL, containing an up and down set.</p>
    </div>
  </div>
</div>
