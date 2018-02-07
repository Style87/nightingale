let template = `
<fieldset>
  <form id="add-project-form">
    <input type="hidden" id="add-project-id" name="id" value="<%= options.project ? options.project.id : '' %>">
  
    <div class="form-group">
      <label for="add-project-name">Name</label>
      <input type="text" class="form-control" id="add-project-name" name="name" placeholder="Name" value="<%= options.project ? options.project.name : 'ENY' %>">
    </div>
  
    <div class="form-group">
      <label for="add-project-path">Project Directory</label>
      <input type="text" class="form-control" id="add-project-path" name="path" placeholder="Path" value="F:\\Development\\eny\\eny-api">
    </div>

    <div class="form-group">
      <label for="add-project-guest-domain">Project Domain</label>
      <input type="text" class="form-control" id="add-project-guest-domain" name="domain" placeholder="Domain" value="<%= options.project ? options.project.domain : 'http://192.168.1.105/eny-api' %>">
    </div>

    <hr>

    <div class="row hidden">
      <div class="col-xs-12 col-md-6">
        <div class="form-group">
          <label for="version-type">MySQL Connection Method</label>
          <div id="mysql-connection-type" class="checkbox">
            <input type="checkbox" data-toggle="toggle" data-width="80" data-off="SSH" data-on="TCP" <%= options.project && !options.project.requireSsh ? 'checked' : 'checked'%>>
          </div>
        </div>
      </div>

      <div class="col-xs-12 col-md-6 hidden">
        <div class="form-group">
          <label for="version-type">Version Storage Type</label>
          <div class="checkbox">
            <input id="version-type" type="checkbox" data-toggle="toggle" data-width="80" data-off="MySQL" data-on="File" checked>
          </div>
        </div>
      </div>
    </div>

    <div id="mysql-ssh-content" class="hidden" style="display:<%= options.project && !options.project.requireSsh ? 'none' : 'block'%>;">
      <div class="form-group">
        <label for="add-project-host">SSH Host</label>
        <input type="text" class="form-control" id="add-project-ssh-host" name="sshhost" placeholder="SSH Host" value="<%= options.project && options.project.requireSsh ? options.project.sshHost : ''%>">
      </div>

      <div class="form-group">
        <label for="add-project-host">SSH Port</label>
        <input type="text" class="form-control" id="add-project-ssh-port" name="sshport" placeholder="SSH Port" value="<%= options.project && options.project.requireSsh ? options.project.sshPort : ''%>">
      </div>

      <div class="form-group">
        <label for="add-project-user">SSH User</label>
        <input type="text" class="form-control" id="add-project-ssh-user" name="sshuser" placeholder="SSH User" value="<%= options.project && options.project.requireSsh ? options.project.sshUser : ''%>">
      </div>

      <div class="form-group">
        <label for="add-project-password">SSH Private Key</label>
        <input type="text" class="form-control" id="add-project-ssh-private-key" name="sshkey" placeholder="SSH Key" value="<%= options.project && options.project.requireSsh ? options.project.sshPrivateKey : ''%>">
      </div>

      <hr>
    </div>

    <div class="form-group">
      <label for="add-project-host">MySQL Host</label>
      <input type="text" class="form-control" id="add-project-host" name="host" placeholder="Host" value="<%= options.project ? options.project.sqlHost : '127.0.0.1' %>">
    </div>

    <div class="form-group">
      <label for="add-project-user">MySQL User</label>
      <input type="text" class="form-control" id="add-project-user" name="user" placeholder="User" value="<%= options.project ? options.project.sqlUser : 'root' %>">
    </div>

    <div class="form-group">
      <label for="add-project-password">MySQL Password</label>
      <input type="text" class="form-control" id="add-project-password" name="password" placeholder="Password" value="<%= options.project ? options.project.sqlPassword : '!PinG13glm+' %>">
    </div>

    <div class="form-group">
      <label for="add-project-database">MySQL Database</label>
      <input type="text" class="form-control" id="add-project-database" name="database" placeholder="Database" value="<%= options.project ? options.project.database : 'ping_dev' %>">
    </div>
    
  </form>
</fieldset>
`;

export { template };