var template = `
<div class="row">
  <% for(var index in collection) { %>
    <div data-id="<%= collection[index].id %>" class="col-xs-12 col-sm-6 col-md-3 project-card select-project">
      <div class="card">
        <div class="row">
          <div class="col-xs-12 text-center">
            <h3><%= collection[index].name %></h3>
          </div>
        </div>
      </div>
    </div>
  <% } %>
  
  <div id="add-project-card" class="col-xs-12 col-sm-6 col-md-3 project-card">
    <div class="card">
      <div class="row">
        <div class="col-xs-12 text-center">
          <span class="fa-stack fa-3x">
            <i class="fa fa-square-o fa-stack-2x"></i>
            <i class="fa fa-plus fa-stack-1x"></i>
          </span>
        </div>
      </div>
    </div>
  </div>
</div>
<div id="add-project-modal-container"></div>
`;

export default template;