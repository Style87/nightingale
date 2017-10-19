var template = `
<div id="<%= migration.id %>" class="row <%= type == 'remove' ? 'migration-card-remove' : '' %> <%= type == 'add' ? 'migration-card-add' : '' %>">
  <div class="col-xs-12 migration-card">
    <div class="card">
      <div class="row">
        <div class="col-xs-12 col-sm-4 col-md-3">
          <div class="form-group">
            <label>Id:</label> <%= migration.id %>
          </div>
        </div>
        <div class="col-xs-12 col-sm-4 col-md-3">
          <div class="form-group">
            <label>Parent Migration Id:</label> <%= migration.parentMigrationId %>
          </div>
        </div>
        <div class="col-xs-12 col-sm-4 col-md-6">
          <div class="form-group">
            <label>Description:</label> <%= migration.description %>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12 col-sm-6">
          <div class="form-group">
            <label>SQL Up:</label> <%= migration.sqlUp %>
          </div>
        </div>
        <div class="col-xs-12 col-sm-6">
          <div class="form-group">
            <label>SQL Down:</label> <%= migration.sqlDown %>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
`;

export { template };