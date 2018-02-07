/***
 *  Exposes
 *  Consumes
 */
 var $ = require('jquery');
var _ = require('underscore');
var Backbone = require('backbone');
import template from '../templates/NavigationTemplate.js';
import { template as projectNameTemplate } from '../templates/NavigationProjectNameTemplate.js';
import AddProjectModalView from './AddProjectModalView.js';
import path from 'path';
import { remote } from 'electron';
import lowdb from 'lowdb';

const app = remote.app;
const dbFile = path.join(app.getPath('userData'), 'db.json');
const fileAsync = require('lowdb/lib/storages/file-async');

var NavigationView = Backbone.View.extend({
  childViews: {},
  id: null,
  // Instead of generating a new element, bind to the existing skeleton of
  // the App already present in the HTML.
  el: '#wrapper',

  // Compile our stats template
  template: _.template(template),

  // Compile our stats template
  projectNameTemplate: _.template(projectNameTemplate),

  // Delegated events for creating new items, and clearing completed ones.
  events: {
    'click .nav-project' : 'onClickProjectName'
  },

  // At initialization we bind to the relevant events on the `Todos`
  // collection, when items are added or changed. Kick things off by
  // loading any preexisting todos that might be saved in *localStorage*.
  initialize: function () {
    var self = this;
    $('body').on('route', function(event){
      var route = Backbone.history.getFragment();
      if (route == '')
      {
        $('#nav-project').addClass('hidden').html('');
        $('.nav-item').hide().prop('href', 'javascript:void(0)');
        $('#breadcrumbs').hide();
      }
      else
      {
        self.id = parseInt(route.replace('project/','').replace(/\/.*$/, ''));
        let db = lowdb(dbFile, fileAsync);
        let project = db.get('projects').find({
          id: self.id
        }).value();

        $('#nav-project').removeClass('hidden').html(self.projectNameTemplate(project));
        $('.nav-item').show();
        $('#breadcrumbs').show();
      }
    })
    // Breadcrumbs
    .on('breadcrumbs.replace', function(event, options){
      $('body').trigger('breadcrumbs.clear');
      $('body').trigger('breadcrumbs.add', [options]);
    })
    .on('breadcrumbs.clear', function(event){
      $('#breadcrumbs').empty();
    })
    .on('breadcrumbs.add', function(event, options){
      if ($('#breadcrumbs').text().length > 0)
      {
        $('#breadcrumbs').append(' <i class="fa fa-chevron-right"></i> ');
      }
      $('#breadcrumbs').append('<a href="'+options.href+'">'+options.text+'</a>');
    })
    .on('breadcrumbs.removeLast', function(event, options){
      $('#breadcrumbs a:last-child').remove();
      if ($('#breadcrumbs').text().length > 0)
      {
        $('#breadcrumbs i:last-child').remove();
      }
    });

    this.childViews.addProjectModal = AddProjectModalView;
  },

  // Re-rendering the App just means refreshing the statistics -- the rest
  // of the app doesn't change.
  render: function () {
    $(this.$el).prepend(this.template);
  },
  
  onClickProjectName: function(e) {
    e.preventDefault();
    e.stopPropagation();

    let db = lowdb(dbFile, fileAsync);
    let project = db.get('projects').find({
      id: this.id
    }).value();

    this.childViews.addProjectModal.setOptions({
      class: 'edit-project-modal',
      title: 'Edit Project',
      project: project
    });
    
    this.childViews.addProjectModal.open();
  },
});

export default NavigationView;