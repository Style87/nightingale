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
      </tr>
    </thead>
    <tbody>
      <% _.forEach(model.migrations, function(migration){ %>
        <tr id="<%= migration.id %>">
          <td><%= migration.id %></td>
          <td><%= migration.description %></td>
          <td><%= migration.parentMigrationId %></td>
        </tr>
      <% }); %>
      <% if (_.size(model.migrations) == 0) { %>
        <tr>
          <td align="middle" colspan="3">No migrations.</td>
        </tr>
      <% } %>
    </tbody>
  </table>
</div>
`;

export default template;