var template = `
<div class="navbar-container">
  <div class="navbar navbar-inverse">
    <div class="container-fluid">
      <div class="navbar-header">
        <a class="navbar-brand" href="#/">Nightingale</a>
      </div>
      <div class="navbar-collapse collapse navbar-inverse-collapse">
        <ul class="nav navbar-nav">
          <li id="versions" class="nav-item disabled"><a disabled="disabled">Versions</a></li>
          <li id="migrations" class="nav-item disabled"><a disabled="disabled">Migrations</a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
          <li><a href="javascript:void(0)" class="nav-project"></a></li>
        </ul>
      </div>
    </div>
  </div>
  <div id="breadcrumbs">
  </div>
</div>
`;

export default template;