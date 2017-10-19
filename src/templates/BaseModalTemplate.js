let template = `
<div class="modal fade" id="<%= options.id %>" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 id="mTitle" class="modal-title"><%= options.title %></h4>
      </div>
      <div class="modal-body">
        <%= options.body %>
      </div>
      <div class="modal-footer">
        <% if (options.showCloseButton) { %>
          <button type="button" class="btn <%= options.closeButtonClass %> btn-raised" data-dismiss="modal"><%= options.closeButtonText %></button>
        <% } %>
        <% if (options.showAffirmButton) { %>
          <button type="button" class="btn <%= options.affirmButtonClass %> btn-raised" <% if (options.closeOnAffirm) { %>data-dismiss="modal"<% } %>>
            <%= options.affirmButtonText %>
          </button>
        <% } %>
      </div>
    </div>
  </div>
</div>
`;

export default template;