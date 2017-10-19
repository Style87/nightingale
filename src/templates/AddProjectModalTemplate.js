let template = `<div class="modal fade" id="<%= options.id %>" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 id="mTitle" class="modal-title">Add Project</h4>
      </div>
      <div class="modal-body">
        <fieldset>
          <form id="add-project-form">
            <div class="form-group">
              <label for="inputEmail">Project Directory</label>
              <input type="text" class="form-control" id="add-project-path" name="path" placeholder="Path">
            </div>

            <div class="form-group">
              <label for="inputEmail">Name</label>
              <input type="text" class="form-control" id="add-project-name" name="name" placeholder="Name">
            </div>

            <div class="form-group">
              <label for="inputEmail">Project Domain</label>
              <input type="text" class="form-control" id="add-project-guest-domain" name="domain" placeholder="Domain">
            </div>

            <div class="form-group">
              <label for="inputEmail">MySQL Host</label>
              <input type="text" class="form-control" id="add-project-host" name="host" placeholder="Host" value="192.168.10.12">
            </div>

            <div class="form-group">
              <label for="inputEmail">MySQL User</label>
              <input type="text" class="form-control" id="add-project-user" name="user" placeholder="User" value="vagrant">
            </div>

            <div class="form-group">
              <label for="inputEmail">MySQL Password</label>
              <input type="text" class="form-control" id="add-project-password" name="password" placeholder="Password" value="vagrant">
            </div>

            <div class="form-group">
              <label for="inputEmail">MySQL Database</label>
              <input type="text" class="form-control" id="add-project-database" name="database" placeholder="Database" value="appetize_ci">
            </div>
          </form>
        </fieldset>
      </div>
      <div class="modal-footer">
        <button type="button" id="close-project-btn" class="btn btn-default text-uppercase" data-dismiss="modal">Close</button>
        <button type="button" id="test-database-btn" class="btn btn-info text-uppercase">test database</button>
        <button type="button" id="add-project-btn" class="btn btn-primary text-uppercase">Save</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="test-database-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 id="mTitle" class="modal-title">Test Database</h4>
      </div>
      <div class="modal-body">
        <p id="test-database-text"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default text-uppercase" data-dismiss="modal">ok</button>
      </div>
    </div>
  </div>
</div>`

export default template;