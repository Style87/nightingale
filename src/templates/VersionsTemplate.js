let template = `
<div class="row">
  <a class="btn btn-default text-uppercase pull-right" href="#/project/<%= nProject.id %>/versions/Create/">create</a>
</div>
<div class="row">
  <table id="table-versions" class="table table-striped table-hover">
    <thead>
      <tr>
        <th>Version</th>
        <th>Migrations</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <% _.forEach(project.versions, function(version, index){ %>
        <tr id="<%= index %>">
          <td><%= index %></td>
          <td><%= _.size(version.migrations) %></td>
          <td><input type="button" class="btn btn-default pull-right migrate <% if (project.version == index) { %> disabled<% } %>" <% if (project.version == index) { %> disabled="true" <% } %> value="Migrate" data-version="<%= version.id %>"></td>
        </tr>
      <% }); %>
      <% if (_.size(project.versions) == 0) { %>
        <tr>
          <td align="middle" colspan="3">No versions.</td>
        </tr>
      <% } %>
    </tbody>
  </table>
</div>
`;

export default template;