let template = `
<fieldset>
  <form id="add-project-form">
    <div class="form-group">
      <label for="add-project-name">Name</label>
      <input type="text" class="form-control" id="add-project-name" name="name" placeholder="Name">
    </div>
  
    <div class="form-group">
      <label for="add-project-path">Project Directory</label>
      <input type="text" class="form-control" id="add-project-path" name="path" placeholder="Path">
    </div>

    <div class="form-group">
      <label for="add-project-guest-domain">Project Domain</label>
      <input type="text" class="form-control" id="add-project-guest-domain" name="domain" placeholder="Domain">
    </div>

    <div class="form-group">
      <label for="add-project-host">MySQL Host</label>
      <input type="text" class="form-control" id="add-project-host" name="host" placeholder="Host" value="192.168.10.12">
    </div>

    <div class="form-group">
      <label for="add-project-user">MySQL User</label>
      <input type="text" class="form-control" id="add-project-user" name="user" placeholder="User" value="vagrant">
    </div>

    <div class="form-group">
      <label for="add-project-password">MySQL Password</label>
      <input type="text" class="form-control" id="add-project-password" name="password" placeholder="Password" value="vagrant">
    </div>

    <div class="form-group">
      <label for="add-project-database">MySQL Database</label>
      <input type="text" class="form-control" id="add-project-database" name="database" placeholder="Database" value="appetize_ci">
    </div>
    
    <div class="form-group">
      <label for="version-type">Version Storage Type</label>
      <div class="checkbox">
        <input id="version-type" type="checkbox" data-toggle="toggle" data-width="80" data-off="MySQL" data-on="File" checked>
      </div>
    </div>
  </form>
</fieldset>
`;

export { template };