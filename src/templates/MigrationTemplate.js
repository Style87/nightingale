let template = `
<div class="row">
  <div class="form-group">
    <label for="description">Description</label>
    <input type="text" class="form-control" id="description" placeholder="Enter description" value="<%= model.get('description')%>">
  </div>
  <div class="form-group">
    <label for="parentMigrationId">Parent Migration</label>
    <select id="parentMigrationId" class="form-control">
      <option value="">NONE</option>
      <% _.forEach(project.migrations, function(migration){ %>
        <% 
          if (model.get('id') == migration.id) {
            return;
          }
        %>
        <option value="<%= migration.id %>" <%= model.get('parentMigrationId') == migration.id ? 'selected="selected"' : '' %>>
          <%= migration.description %>
        </option>
      <% }) %>
    </select>
  </div>
  <div class="form-group">
    <label for="sqlUp">SQL Up</label>
    <textarea id="sqlUp" class="form-control" rows="5"><%= model.get('sqlUp')%></textarea>
  </div>
  <div class="form-group">
    <label for="sqlDown">SQL Down</label>
    <textarea id="sqlDown" class="form-control" rows="5"><%= model.get('sqlDown')%></textarea>
  </div>
  
  <button id="btn-save" class="btn btn-primary text-uppercase">save</button>
</div>
`;

export default template;