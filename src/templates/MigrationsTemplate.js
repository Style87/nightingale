let template = `
<div class="row">
  <a id="btn-create-migration" class="btn btn-default text-uppercase pull-right" href="#/project/<%= project.id %>/migrations/Create/">create</a>
</div>
<div class="row">
  <table id="table-migrations" class="table table-striped table-hover">
    <thead>
      <tr>
        <th>Id</th>
        <th>Description</th>
        <th>Parent Migration Id</th>
        <th>Has Run</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <% _.forEach(model.migrations, function(migration){ %>
        <tr id="<%= migration.id %>">
          <td><%= migration.id %></td>
          <td><%= migration.description %></td>
          <td><%= migration.parentMigrationId %></td>
          <td>
            <% if(migration.hasRun) { %>
              <i class="fa fa-check text-success"></i>
            <% } %>
          </td>
          <td><input type="button" class="btn btn-default pull-right migrate" value="Migrate" data-id="<%= migration.id %>"></td>
        </tr>
      <% }); %>
      <% if (_.size(model.migrations) == 0) { %>
        <tr>
          <td align="middle" colspan="5">No migrations.</td>
        </tr>
      <% } %>
    </tbody>
  </table>
</div>
`;

export default template;