/***
 *  Exposes
 *  Consumes
 */
import 'jquery';
var _ = require('underscore');
var Backbone = require('backbone');
import BaseView from '../core/BaseView.js';

import template from '../templates/HomeTemplate.js';

import AddProjectModalView from './AddProjectModalView.js';
import lowdb from 'lowdb';
import fs from 'fs';
import path from 'path';
import { remote } from 'electron';

const app = remote.app;
const dbFile = path.join(app.getPath('userData'), 'db.json');
const fileAsync = require('lowdb/lib/storages/file-async');

var HomeView = BaseView.extend({
  name: 'HomeView',
  childViews: {},
  el: '#content',

  template: _.template(template),

  events: {
    'click #add-project-card' : 'openAddProjectModal',
    'click .select-project': 'onSelectProject',
    'change #file-input' : 'onFileSelect',
  },

  initialize: function() {
    var self = this;

    this.childViews.addProjectModal = AddProjectModalView;
    $(document).on('hidden.bs.modal', '.add-project-modal', function(){
      self.render();
    })
    
    BaseView.prototype.initialize.call(this);
  },

  render: function () {
    this.db = lowdb(dbFile, { storage: fileAsync })
    this.collection = this.db.get('projects').value();
    return BaseView.prototype.render.call(this);
  },

  afterRender: function(){
  },

  openAddProjectModal: function(e) {
    e.preventDefault();
    e.stopPropagation();
    this.childViews.addProjectModal.setOptions({
      class: 'add-project-modal'
    })
    this.childViews.addProjectModal.open();
  },

  onFileSelect: function() {
    $('#add-project-path').text($('#file-input').get(0).files[0].path);
  },

  onSelectProject: function(e) {
    var projectId = $(e.currentTarget).data('id')
      , projectUri = '#project/' + projectId
      , nProject = this.db.get('projects').find({ id: parseInt(projectId) }).value()
      , projectDb = lowdb(path.resolve(nProject.path, 'nightingale', 'meta', 'db.json'), { storage: fileAsync })
      , project = projectDb.getState()
      , projectMigrations = project.migrations
      , projectVersions = project.versions
      , maxVersion = project.maxVersion
      , self = this;

    // Set loading
    this.setLoading(e);

    // Sync project non-versioned migrations
    var dir = path.resolve(nProject.path, 'nightingale', 'migrations');
    fs.readdirSync(dir, 'utf8').forEach(function(file){
      if (!file.match(/\.json$/)) {
        return;
      }
      var migrationId = file.replace('.json', '')
        , migration = _.findWhere(projectMigrations, { id: migrationId });
      if (typeof migration === 'undefined') {
        migration = JSON.parse(fs.readFileSync(path.resolve(dir, file), "utf8"));
        migration.hasRun = false;
        projectMigrations[migration.id] = migration;
        console.log('Added migration ' + migrationId);
      }
    })

    // Sync project versions
    var dir = path.resolve(nProject.path, 'nightingale', 'versions')
        , versions = fs.readdirSync(dir, 'utf8');
    if (versions[versions.length-1] > maxVersion) {
      versions.forEach(function(version){
        if (parseInt(version) >= project.maxVersion) {
          var projectVersion = {};
          fs.readdirSync(path.resolve(dir, version), 'utf8').forEach(function(file){
            var migrationId = file.replace('.json', '')
              , migration = JSON.parse(fs.readFileSync(path.resolve(dir, version, file), "utf8"));
              migration.hasRun = false;
              projectVersion[migrationId] = migration;
          })
          projectVersions[parseInt(version)] = projectVersion;
          console.log('Added version ' + version);
        }
      })

      // Update the max version
      project.maxVersion = parseInt(versions[versions.length-1]);
    }

    // Save migrations
    this.db
      .get('projects')
      .write();

    $('.nav #versions a').prop('href', projectUri + '/versions/');
    $('.nav #migrations a').prop('href', projectUri + '/migrations/');

    Backbone.history.navigate(projectUri + '/migrations/', { trigger : true });
  },

  setLoading: function(e) {
    
  },
});

export default HomeView;