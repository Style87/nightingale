var template = `
<% if (model.get('id') == null) { %>
  <div class="row">
    <div class="col-xs-12">
      <button id="btn-save-version" class="btn btn-default text-uppercase pull-right disabled" disabled="true">save</button>
    </div>
  </div>
<div class="spacer"></div>
<% } %>
<h2>Migrations</h2>
<div id="versioned-migrations">
  <% _.forEach(model.get('migrations'), function(migration){ %>
    <%= _.template(migrationCardTemplate)({
      type: model.get('id') == null ? 'remove' : '',
      migration: migration
    }) %>
  <% }); %>
</div>
<% if (model.get('id') == null) { %>
  <h2>Add Migrations</h2>
  <div id="unversioned-migrations">
    <% _.forEach(project.migrations, function(migration){ %>
        <%= _.template(migrationCardTemplate)({
          type: 'add',
          migration: migration
        }) %>
    <% }); %>
  </div>
<% } %>
`;

export default template;
