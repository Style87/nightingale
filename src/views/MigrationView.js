/***
 *  Exposes
 *  Consumes
 */
import 'jquery';
var _ = require('underscore');
var Backbone = require('backbone');
var codemirror = require('codemirror');
require('codemirror/mode/sql/sql');
import BaseView from '../core/BaseView.js';
import Migration from '../models/Migration.js';
import template from '../templates/MigrationTemplate.js';
import lowdb from 'lowdb';
import fs from 'fs';
import mysql from 'mysql';
import path from 'path';
import { remote } from 'electron';

const app = remote.app;
const dbFile = path.join(app.getPath('userData'), 'db.json');
const fileAsync = require('lowdb/lib/storages/file-async');

var MigrationView = BaseView.extend({
  editorSqlUp: null,
  editorSqlDown: null,

  el: '#content',

  template: _.template(template),

  events: {
    'click #btn-save': 'onClickSave'
  },

  initialize: function(options) {
    this.options = options;
    let db = lowdb(dbFile, { storage: fileAsync })
      , nProject = db.get('projects').find({id: parseInt(options.projectId) }).value()
      , projectDb = lowdb(path.resolve(nProject.path, 'nightingale', 'meta', 'db.json'), { storage: fileAsync });
    this.project = projectDb.getState();

    this.model = new Migration();
    if (options.migrationId != null)
    {
      this.model.set({id: options.migrationId});
      this.model.fetch({
        projectId: options.projectId
      });
    }
  },

  afterRender: function() {
    this.editorSqlUp = codemirror.fromTextArea(document.getElementById('sqlUp'), {
      mode: 'text/x-sql',
      lineNumbers: true,
    });
    this.editorSqlDown = codemirror.fromTextArea(document.getElementById('sqlDown'), {
      mode: 'text/x-sql',
      lineNumbers: true,
    });
  },

  onClickSave: function(e) {
    e.stopPropagation();
    e.preventDefault();
    var self = this;

    this.model.save(
      {
        description: $('#description').val(),
        sqlUp: self.editorSqlUp.doc.getValue(),
        sqlDown: self.editorSqlDown.doc.getValue(),
        parentMigrationId: $('#parentMigrationId option:selected').val() == '' ? null : $('#parentMigrationId option:selected').val(),
      },
      {
        projectId: parseInt(this.options.projectId),
        success: function(model) {
          console.log('SUCCESS');
          Backbone.history.navigate('#/project/' + self.options.projectId + '/migrations/', {trigger: true});
        },
        error: function (model, response) {
          console.log("error");
        }
      }
    );
  },
});

export default MigrationView;