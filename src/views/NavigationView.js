/***
 *  Exposes
 *  Consumes
 */
 var $ = require('jquery');
var _ = require('underscore');
var Backbone = require('backbone');
import template from '../templates/NavigationTemplate.js';
import path from 'path';
import { remote } from 'electron';
import lowdb from 'lowdb';

const app = remote.app;
const dbFile = path.join(app.getPath('userData'), 'db.json');
const fileAsync = require('lowdb/lib/storages/file-async');

var NavigationView = Backbone.View.extend({

  // Instead of generating a new element, bind to the existing skeleton of
  // the App already present in the HTML.
  el: '#wrapper',

  // Compile our stats template
  template: _.template(template),

  // Delegated events for creating new items, and clearing completed ones.
  events: {
    'click .nav-project' : 'onClickProjectName'
  },

  // At initialization we bind to the relevant events on the `Todos`
  // collection, when items are added or changed. Kick things off by
  // loading any preexisting todos that might be saved in *localStorage*.
  initialize: function () {
    $('body').on('route', function(event){
      var route = Backbone.history.getFragment();
      if (route == '')
      {
        $('.nav-project').text('');
        $('.nav-item').addClass('disabled');
        $('.nav-item a').prop('disabled', true);
        $('#breadcrumbs').hide();
      }
      else
      {
        let id = parseInt(route.replace('project/','').replace(/\/.*$/, ''));
        let db = lowdb(dbFile, fileAsync);
        let project = db.get('projects').find({
          id: id
        }).value();
        $('.nav-project').text(project.name);
        $('.nav-item').removeClass('disabled');
        $('.nav-item a').prop('disabled', false);
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
  },

  // Re-rendering the App just means refreshing the statistics -- the rest
  // of the app doesn't change.
  render: function () {
    $(this.$el).prepend(this.template);
  },
  
  onClickProjectName: function(e) {
    e.preventDefault();
    e.stopPropagation();
  },
});

export default NavigationView;