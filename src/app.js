// Here is the starting point for your application code.

var $ = require('jquery');
window.$ = $;
window.jQuery = $;
//require('bootstrap');

var Backbone = require('backbone');

import Router from './router.js';
import ContentView from './views/ContentView.js';
import NavigationView from './views/NavigationView.js';
import lowdb from 'lowdb';
import path from 'path';
import { remote } from 'electron';
import env from './env';

// helpers
import './helpers/context_menu.js';
import './helpers/external_links.js';

const fileAsync = require('lowdb/lib/storages/file-async');
const app = remote.app;
const dbFile = path.join(app.getPath('userData'), 'db.json');

// Start database using file-async storage
// For ease of use, read is synchronous
const db = lowdb(dbFile, {
  storage: fileAsync
});

// Initialize the application view
$(function() {
  // Fix multiple open modal backdrops
  $(document).on('show.bs.modal', '.modal', function () {
    var zIndex = 1040 + (10 * $('.modal:visible').length);
    $(this).css('z-index', zIndex);
    setTimeout(function() {
      $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
    }, 0);
  });

  $(document).on('hidden.bs.modal', '.modal', function () {
    $('.modal:visible').length && $(document.body).addClass('modal-open');
  });

  $('body').on('click', 'a', function(e){
    e.preventDefault();
    e.stopPropagation();
    window.App.Router.navigate($(e.currentTarget).prop('hash'), { trigger : true });
  })

  window.App = {
    Models: {},
    Collections: {},
    Views: {},
    Router: new Router()
  };

  db.defaults({ projects: [] }).write().then(function(){
    window.App.Views.navigationView = new NavigationView();
    window.App.Views.navigationView.render();
  
    window.App.Views.contentView = new ContentView();
    window.App.Views.contentView.render();

    $('#splash').hide();

    /* Start history after the app view has been rendered */
    Backbone.history.start();
  });
});