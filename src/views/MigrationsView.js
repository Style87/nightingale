/***
 *  Exposes
 *  Consumes
 */
import 'jquery';
var _ = require('underscore');
var Backbone = require('backbone');
import BaseView from '../core/BaseView.js';
import Migration from '../models/Migration.js'

import template from '../templates/MigrationsTemplate.js';
import lowdb from 'lowdb';
import path from 'path';
import { remote } from 'electron';
import mysql from 'mysql';

const app = remote.app;
const dbFile = path.join(app.getPath('userData'), 'db.json');
const fileAsync = require('lowdb/lib/storages/file-async');
const fileSync = require('lowdb/lib/storages/file-sync');

var MigrationsView = BaseView.extend({
  el: '#content',

  template: _.template(template),

  events: {
    'click #table-migrations tr' : 'onClickMigration',
    'click #btn-create-migration' : 'onClickCreateMigration',
    'click .migrate': 'onClickMigrate'
  },

  initialize: function(options) {
    this.options = options;
  },

  render: function () {
    let db = lowdb(dbFile, { storage: fileAsync });
    this.project = db.get('projects').find({ id: parseInt(this.options.projectId) }).value();
    let projectDb = lowdb(path.resolve(this.project.path, 'nightingale', 'meta', 'db.json'), fileAsync);
    this.model = projectDb.getState();

    return BaseView.prototype.render.call(this);
  },

  onClickMigration: function(e) {
    e.stopPropagation();
    if ($(e.currentTarget).prop('id') == '') {
      return false;
    }
    window.App.Router.navigate('#project/'+this.options.projectId + '/migrations/' + $(e.currentTarget).prop('id') + '/', { trigger : true });
  },
  
  onClickMigrate: function(e) {
    e.stopPropagation();
    e.preventDefault();

    $('.migrate').addClass('disabled').prop('disabled', true);

    let self = this
      , db = lowdb(dbFile, { storage: fileSync })
      , nProject = db.get('projects').find({id: this.options.projectId }).value()
      , projectDb = lowdb(path.resolve(nProject.path, 'nightingale', 'meta', 'db.json'), { storage: fileSync })
      , project = projectDb.getState()
      , migration = new Migration({
        id: $(e.currentTarget).data('id')
      })
      , con = mysql.createConnection({
        host: nProject.sqlHost,
        user: nProject.sqlUser,
        password: nProject.sqlPassword,
        database: nProject.database,
        multipleStatements: true
      });
    migration.projectId = this.options.projectId;

    migration.fetch({
      projectId: this.options.projectId
    });

    $('body').one('Migration.MigrationComplete', function(){
      self.render();
    });

    con.connect(function(err){
      if (err) {
        console.log(err);
        throw err;
      }

      if (migration.get('hasRun')) {
        project = migration.migrateDown(con);
      }
      else {
        project = migration.migrateUp(con);
      }

      projectDb.setState(project);

      con.end();
      console.info('Migration complete');
      $('body').trigger('Migration.MigrationComplete')
    });
  },
});

export default MigrationsView;