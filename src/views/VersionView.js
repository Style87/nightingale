/***
 *  Exposes
 *  Consumes
 */
import 'jquery';
var _ = require('underscore');
var Backbone = require('backbone');
import BaseView from '../core/BaseView.js';
import Version from '../models/Version.js'
import template from '../templates/VersionTemplate.js';
import { template as MigrationCardTemplate } from '../templates/MigrationCardTemplate.js';
import lowdb from 'lowdb';
import fs from 'fs';
import jetpack from 'fs-jetpack';
import mysql from 'mysql';
import path from 'path';
import { remote } from 'electron';

const app = remote.app;
const dbFile = path.join(app.getPath('userData'), 'db.json');
const fileAsync = require('lowdb/lib/storages/file-async');

var VersionView = BaseView.extend({
  el: '#content',

  template: _.template(template),

  events: {
    'click .migration-card-add': 'onClickAddMigration',
    'click .migration-card-remove': 'onClickRemoveMigration',
    'click #btn-save-version': 'onClickSave'
  },

  initialize: function(options) {
    this.options = options;
    let db = lowdb(dbFile, { storage: fileAsync })
      , nProject = db.get('projects').find({ id: parseInt(options.projectId) }).value()
      , projectDb = lowdb(path.resolve(nProject.path, 'nightingale', 'meta', 'db.json'), { storage: fileAsync })
      , project = projectDb.getState();

    this.project = $.extend({}, project, nProject);
    this.migrationCardTemplate = MigrationCardTemplate;

    this.model = new Version();
    if (options.versionId != null)
    {
      this.model.set({id: options.versionId});
      this.model.fetch({
        projectId: options.projectId,
      });
    }
  },

  onClickAddMigration: function(e) {
    e.stopPropagation();

    var id = $(e.currentTarget).prop('id');
    this.addMigration(id);
    $('#btn-save-version').removeClass('disabled').prop('disabled', false);
  },

  addMigration: function(id) {
    let db = lowdb(dbFile, { storage: fileAsync })
      , nProject = db.get('projects').find({ id: parseInt(this.options.projectId) }).value()
      , projectDb = lowdb(path.resolve(nProject.path, 'nightingale', 'meta', 'db.json'), { storage: fileAsync })
      , migration = projectDb.get('migrations').find({id: id}).value();

    if (migration.parentMigrationId != null && $('#versioned-migrations > #'+migration.parentMigrationId).length == 0) {
      this.addMigration(migration.parentMigrationId);
    }

    $('#'+id).remove();
    $('#versioned-migrations').append(_.template(MigrationCardTemplate)({
      type: 'remove',
      migration: this.project.migrations[id]
    }));
  },

  onClickRemoveMigration: function(e) {
    e.stopPropagation();

    var id = $(e.currentTarget).prop('id');
    this.removeMigration(id);
    if ($('.migration-card-remove').length == 0) {
      $('#btn-save-version').addClass('disabled').prop('disabled', true);
    }
  },

  removeMigration: function(id) {
    let self = this
      , db = lowdb(dbFile, { storage: fileAsync })
      , nProject = db.get('projects').find({ id: parseInt(this.options.projectId) }).value()
      , projectDb = lowdb(path.resolve(nProject.path, 'nightingale', 'meta', 'db.json'), { storage: fileAsync })
      , childMigrations = projectDb.get('migrations').filter({parentMigrationId: id}).value();

    _.each(childMigrations, function(migration){
      if ($('#unversioned-migrations > #' + migration.id).length == 0) {
        self.removeMigration(migration.id);
      }
    })

    $('#'+id).remove();
    $('#unversioned-migrations').append(_.template(MigrationCardTemplate)({
      type: 'add',
      migration: this.project.migrations[id]
    }));

    
  },

  onClickSave: function(e) {
    e.stopPropagation();
    e.preventDefault();

    let self = this
      , db = lowdb(dbFile, { storage: fileAsync })
      , nProject = db.get('projects').find({ id: parseInt(this.options.projectId) }).value()
      , projectDb = lowdb(path.resolve(nProject.path, 'nightingale', 'meta', 'db.json'), { storage: fileAsync })
      , project = projectDb.getState()
      , newVersion = _.size(project.versions)
      , projectVersionDirectory = path.resolve(nProject.path, 'nightingale', 'versions', newVersion)
      , projectMigrationDirectory = path.resolve(nProject.path, 'nightingale', 'migrations')
      , version = {
        id: newVersion,
        migrations: {}
      };

    jetpack.dir(projectVersionDirectory);

    $('.migration-card-remove').each(function(index, element){
      var migrationId = $(element).prop('id');
      version.migrations[migrationId] = project.migrations[migrationId];
      version.migrations[migrationId].version = newVersion;
      delete project.migrations[migrationId];
      jetpack.move(path.resolve(projectMigrationDirectory, migrationId + '.json'), path.resolve(projectVersionDirectory, migrationId + '.json'))
    });
    
    // Remove the parent migration link for any children not added to the version
    _.each(version.migrations, function(migration){
      var childMigrations = _.filter(project.migrations, {parentMigrationId: migration.id});
      _.each(childMigrations, function(childMigration){
        childMigration.parentMigrationId = null;
      });
    });
    
    project.versions[newVersion] = version;
    project.maxVersion = newVersion;
    projectDb.write().then(function(){
      Backbone.history.navigate('#/project/' + self.options.projectId + '/versions/', {trigger: true});
    });
  },
});

export default VersionView;