/***
 *  Exposes
 *  Consumes
 */
import 'jquery';
var _ = require('underscore');
var Backbone = require('backbone');
import BaseView from '../core/BaseView.js';

import template from '../templates/MigrationsTemplate.js';
import lowdb from 'lowdb';
import path from 'path';
import { remote } from 'electron';

const app = remote.app;
const dbFile = path.join(app.getPath('userData'), 'db.json');
const fileAsync = require('lowdb/lib/storages/file-async');

var MigrationsView = BaseView.extend({
  el: '#content',

  template: _.template(template),

  events: {
    'click #table-migrations tr' : 'onClickMigration',
    'click #btn-create-migration' : 'onClickCreateMigration'
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
});

export default MigrationsView;