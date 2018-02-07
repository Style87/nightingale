/***
 *  Exposes
 *  Consumes
 *    Version.MigrationComplete
 */
import 'jquery';
var _ = require('underscore');
var Backbone = require('backbone');
import BaseView from '../core/BaseView.js';
import Version from '../models/Version.js';
import template from '../templates/VersionsTemplate.js';
import lowdb from 'lowdb';
import fs from 'fs';
import mysql from 'mysql';
import path from 'path';
import { remote } from 'electron';

const { exec } = require('child_process');
const app = remote.app;
const dbFile = path.join(app.getPath('userData'), 'db.json');
const fileAsync = require('lowdb/lib/storages/file-async');

var VersionsView = BaseView.extend({
  el: '#content',

  template: _.template(template),

  events: {
    'click #table-versions tr' : 'onClickVersion',
    'click .migrate' : 'onClickMigrate',
  },

  initialize: function(options) {
    this.options = options;
  },

  render: function () {
    let db = lowdb(dbFile, { storage: fileAsync });
    this.nProject = db.get('projects').find({ id: parseInt(this.options.projectId) }).value();
    let projectDb = lowdb(path.resolve(this.nProject.path, 'nightingale', 'meta', 'db.json'), { storage: fileAsync });
      
    this.project = projectDb.getState();

    return BaseView.prototype.render.call(this);
  },
  
  onClickVersion: function(e) {
    e.stopPropagation();
    window.App.Router.navigate('#/project/'+this.options.projectId+'/versions/'+$(e.currentTarget).prop('id')+'/', { trigger: true });
  },
  
  onClickMigrate: function(e) {
    e.stopPropagation();
    e.preventDefault();
    
    $(e.currentTarget).addClass('disabled').prop('disabled', true);
    
    let self = this
      , versionId = $(e.currentTarget).data('version')
      , version = new Version();

    version.set({
      id: versionId
    })
    version.fetch({
      projectId: this.options.projectId
    });

    $('body').one('Version.MigrationComplete', function(){
      $(e.currentTarget).removeClass('disabled').prop('disabled', false);
      Backbone.history.navigate('#/project/' + self.options.projectId + '/versions/', {trigger: true});
    });
    version.migrateTo();
  },
});

export default VersionsView;