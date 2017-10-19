/***
 *  Exposes
 *  Consumes
 */
import 'jquery';
var _ = require('underscore');
var Backbone = require('backbone');
import BaseView from '../core/BaseView.js';

import template from '../templates/ProjectTemplate.js';
import lowdb from 'lowdb';
import fs from 'fs';
import mysql from 'mysql';
import path from 'path';
import { remote } from 'electron';

const app = remote.app;
const dbFile = path.join(app.getPath('userData'), 'db.json');

var ProjectView = BaseView.extend({
  el: '#content',

  template: _.template(template),

  events: {
  },

  initialize: function(options) {
    var self = this;
    this.db = lowdb(dbFile, { storage: require('lowdb/lib/storages/file-async') });
    // The id is a string in the url so ensure it's an int
    this.model = this.db.get('projects').find({ id: parseInt(options.id) }).value();

    $('.nav-project').text(this.model.name);
  },

  render: function () {
    return BaseView.prototype.render.call(this);
  }
});

export default ProjectView;