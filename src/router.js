let $ = require('jquery');
var Backbone = require('backbone');
import BaseRouter from './core/BaseRouter.js';
import HomeView from './views/HomeView.js';
import ProjectView from './views/ProjectView.js';
import VersionsView from './views/VersionsView.js';
import VersionView from './views/VersionView.js';
import MigrationsView from './views/MigrationsView.js';
import MigrationView from './views/MigrationView.js';

var Router = BaseRouter.extend({

  routes : {
    'project/:projectId(/)' : 'showProject',
    'project/:projectId/versions(/)' : 'showVersions',
    'project/:projectId/versions/:versionId(/)' : 'showVersion',
    'project/:projectId/migrations(/)' : 'showMigrations',
    'project/:projectId/migrations/:migrationId(/)' : 'showMigration',
    'project/:projectId/schema(/)' : 'showSchema',
    '*default' : 'showHome'
  },
  currentView: null,
  // Routes that need authentication and if user is not authenticated
  // gets redirect to login page
  requresAuth : [],

  // Routes that should not be accessible if user is authenticated
  // for example, login, register, forgetpasword ...
  preventAccessWhenAuth : [],

  before : function(params, next){
    return next();
  },

  after : function(){
      //empty
  },

  changeView : function(view){
    var self = this;
      //Close is a method in BaseView
      //that check for childViews and 
      //close them before closing the 
      //parentView
      function setView(view){
          if(self.currentView){
              self.currentView.close();
          }
          self.currentView = view;
          view.render();
      }

      setView(view);
  },

  fetchError : function(error){},
  
  //... Route handlers …
  showHome:function(){
    $('.nav > .active').removeClass('active');
    $('body').trigger('breadcrumbs.clear');
    this.changeView(new HomeView());
  },
  showProject:function(projectId){
    this.changeView(new ProjectView({
      id: projectId
    }));
  },
  showVersions:function(projectId){
    $('.nav > .active').removeClass('active');
    $('.nav #versions').addClass('active');
    $('body').trigger('breadcrumbs.replace', [{
      text: 'Versions',
      href: '#/project/'+projectId+'/versions/'
    }]);
    this.changeView(new VersionsView({
      projectId: projectId
    }));
  },
  showVersion:function(projectId, versionId){
    $('.nav > .active').removeClass('active');
    $('.nav #versions').addClass('active');
    $('body')
      .trigger('breadcrumbs.replace', [{
          text: 'Versions',
          href: '#/project/'+projectId+'/versions/'
        }])
      .trigger('breadcrumbs.add', [{
        text: versionId,
        href: '#/project/'+projectId+'/versions/'+versionId+'/'
      }]);
    if (versionId == 'Create') {
      versionId = null
    }
    this.changeView(new VersionView({
      projectId: projectId,
      versionId: versionId
    }));
  },
  showMigrations:function(projectId){
    $('.nav > .active').removeClass('active');
    $('.nav #migrations').addClass('active');
    $('body').trigger('breadcrumbs.replace', [{
      text: 'Migrations',
      href: '#/project/'+projectId+'/migrations/'
    }]);
    this.changeView(new MigrationsView({
      projectId: parseInt(projectId)
    }));
  },
  showMigration:function(projectId, migrationId){
    $('.nav > .active').removeClass('active');
    $('.nav #migrations').addClass('active');
    $('body')
      .trigger('breadcrumbs.replace', [{
          text: 'Migrations',
          href: '#/project/'+projectId+'/migrations/'
        }])
      .trigger('breadcrumbs.add', [{
        text: migrationId,
        href: '#/project/'+projectId+'/migrations/'+migrationId+'/'
      }]);
    if (migrationId == 'Create') {
      migrationId = null
    }
    this.changeView(new MigrationView({
      projectId: projectId,
      migrationId: migrationId
    }));
  },
  showSchema:function(projectId){
    $('.nav > .active').removeClass('active');
    $('.nav #schema').addClass('active');
    $('body')
      .trigger('breadcrumbs.replace', [{
          text: 'Schema',
          href: '#/project/'+projectId+'/schema/'
        }]);
    if (migrationId == 'Create') {
      migrationId = null
    }
    this.changeView(new SchemaView({
      projectId: parseInt(projectId)
    }));
  },
});
  
export default Router;