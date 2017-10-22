/*global define*/
import 'jquery';
var _ = require('underscore');
var Backbone = require('backbone');
require('jquery-validation');
require('bootstrap-toggle');
import BackboneModal from '../core/BackboneModal/BackboneModal.js';
import BaseModalView from '../core/BaseModalView.js';
import { script as MigrateScript } from '../core/PhpScripts/Migrate.js';
import { script as AdapterMySqlScript } from '../core/PhpScripts/Adapter_MySQL.js';
import { script as MigrationScript } from '../core/PhpScripts/Migration.js';
import { script as ProjectScript } from '../core/PhpScripts/Project.js';
import { script as VersionScript} from '../core/PhpScripts/Version.js';

import { script as postCheckoutHookScript } from '../core/PostCheckoutHookScript.js';
import { script as GitIgnore } from '../core/GitIgnore.js';
import { template as AddProjectModalHeaderTemplate } from '../templates/AddProjectModalHeaderTemplate.js';
import { template as AddProjectModalBodyTemplate } from '../templates/AddProjectModalBodyTemplate.js';
import { template as AddProjectModalFooterTemplate } from '../templates/AddProjectModalFooterTemplate.js';
import TestDatabaseModalView from './TestDatabaseModalView.js';
import lowdb from 'lowdb';
import jetpack from 'fs-jetpack';
import path from 'path';
import mysql from 'mysql';
import { remote } from 'electron';

const app = remote.app;
const dbFile = path.join(app.getPath('userData'), 'db.json');
const fileAsync = require('lowdb/lib/storages/file-async');

var AddProjectModalView = new BackboneModal({
  validator: null,

  headerTemplate: _.template(AddProjectModalHeaderTemplate),
  bodyTemplate: _.template(AddProjectModalBodyTemplate),
  footerTemplate: _.template(AddProjectModalFooterTemplate),
  class:'add-project-modal',

  events: {
    'click #test-database-btn' : function(e) {
      e.preventDefault();
      e.stopPropagation();
      var self = this;
      var $button = $(e.currentTarget);
      $button.addClass('disabled').prop('disabled', true);
      var con = mysql.createConnection({
        host: $('#add-project-host').val(),
        user: $('#add-project-user').val(),
        password: $('#add-project-password').val(),
        database: $('#add-project-database').val()
      });
  
      con.connect(function(err){
        if (err)
        {
          self.testDatabaseModal.setOptions({
            "class": 'text-danger',
            "text": 'Test failed.',
          });
        }
        else
        {
          self.testDatabaseModal.setOptions({
            "class": 'text-success',
            "text": 'Test successful.',
          });
          con.end();
        }
        self.testDatabaseModal.open();
        $button.removeClass('disabled').prop('disabled', false);
      });
    },
    'click #add-project-btn': function() {
      var self = this
        , id = Date.now();
      $('#add-project-btn, #close-project-btn').addClass('disabled').prop('disabled', true);
  
      // if (!$('#add-project-form').valid())
      // {
      //   $('#add-project-btn, #close-project-btn').removeClass('disabled').prop('disabled', false);
      //   return false;
      // }
  
      var project = this.db.get('projects').find({path:$('#add-project-path').val()}).value();
  
      if (typeof project != 'undefined')
      {
        var modal = new BaseModalView({
          title: 'Project exists',
          body: 'The given path already has a Nightingale project.',
          showAffirmButton: false,
          afterRender: function() {
            var self = this;
            $('#'+this.options.id).on('hidden.bs.modal', function(){
              $('#add-project-btn, #close-project-btn').removeClass('disabled').prop('disabled', false);
              self.close();
            });
          }
        });
        modal.render();
        modal.show();
        return false;
      }
  
      this.db
        .get('projects')
        .push({
          id: id,
          path: $('#add-project-path').val(),
          name: $('#add-project-name').val(),
          domain: $('#add-project-guest-domain').val(),
          host: $('#add-project-host').val(),
          user: $('#add-project-user').val(),
          password: $('#add-project-password').val(),
          database: $('#add-project-database').val()
        })
        .write();
      var project = this.db.get('projects').find({id:id}).value();

      var projectNightingaleDirectory = path.resolve(project.path, 'nightingale');
      var projectNightingaleMetaDirectory = path.resolve(projectNightingaleDirectory, 'meta');
      var projectNightingaleVersionsDirectory = path.resolve(projectNightingaleDirectory, 'versions');
      var projectNightingaleMigrationsDirectory = path.resolve(projectNightingaleDirectory, 'migrations');
      var projectNightingaleDbFile = path.resolve(projectNightingaleMetaDirectory, 'db.json');
      
      var projectNightingaleAdapterMySqlPhpFile = path.resolve(projectNightingaleMetaDirectory, 'Adapter_MySQL.php');
      var projectNightingaleConfigPhpFile = path.resolve(projectNightingaleMetaDirectory, 'config.php');
      var projectNightingaleMigratePhpFile = path.resolve(projectNightingaleMetaDirectory, 'Migrate.php');
      var projectNightingaleMigrationPhpFile = path.resolve(projectNightingaleMetaDirectory, 'Migration.php');
      var projectNightingaleProjectPhpFile = path.resolve(projectNightingaleMetaDirectory, 'Project.php');
      var projectNightingaleVersionPhpFile = path.resolve(projectNightingaleMetaDirectory, 'Version.php');
      
      var projectNightingaleGitPostCheckoutHookFile = path.resolve(project.path, '.git', 'hooks', 'post-checkout');
      
      var projectNightingaleGitIgnoreFile = path.resolve(project.path, '.gitignore');

      let isExists = jetpack.exists(projectNightingaleDirectory);

      // Create the nightingale project directories
      jetpack.dir(projectNightingaleDirectory);
      jetpack.dir(projectNightingaleMetaDirectory);
      jetpack.dir(projectNightingaleVersionsDirectory);
      jetpack.dir(projectNightingaleMigrationsDirectory);

      let projectDb = lowdb(path.join(projectNightingaleMetaDirectory, 'db.json'), {
        storage: fileAsync
      });

      // Create the php scripts
      jetpack.file(projectNightingaleAdapterMySqlPhpFile);
      jetpack.write(projectNightingaleAdapterMySqlPhpFile, AdapterMySqlScript);
      
      jetpack.file(projectNightingaleConfigPhpFile);
      let configScript = _.template(ConfigScript);
      jetpack.write(projectNightingaleConfigPhpFile, configScript({
        host: project.host,
        port: 3306,
        user: project.user,
        password: project.password,
        database: project.database
      }));
      
      jetpack.file(projectNightingaleMigratePhpFile);
      jetpack.write(projectNightingaleMigratePhpFile, MigrateScript);
      
      jetpack.file(projectNightingaleMigrationPhpFile);
      jetpack.write(projectNightingaleMigrationPhpFile, MigrationScript);
      
      jetpack.file(projectNightingaleProjectPhpFile);
      jetpack.write(projectNightingaleProjectPhpFile, ProjectScript);
      
      jetpack.file(projectNightingaleVersionPhpFile);
      jetpack.write(projectNightingaleVersionPhpFile, VersionScript);

      // Create the post-checkout hooks
      if (!jetpack.exists(projectNightingaleGitPostCheckoutHookFile)) {
        jetpack.file(projectNightingaleGitPostCheckoutHookFile);
        jetpack.write(projectNightingaleGitPostCheckoutHookFile, '#!/bin/bash');
      }

      jetpack.append(projectNightingaleGitPostCheckoutHookFile, postCheckoutHookScript);

      // Add git ignore for the db.json and config.php files.
      if (!jetpack.exists(projectNightingaleGitIgnoreFile)) {
        jetpack.file(projectNightingaleGitIgnoreFile);
      }
      
      if (!isExists) {
        jetpack.append(projectNightingaleGitIgnoreFile, GitIgnore);
      }

      // Read all migrations for db.json
      let migrationFiles = jetpack.list(projectNightingaleMigrationsDirectory)
        , migrations = {};
      
      _.each(migrationFiles, function(migrationFile){
        if (migrationFile == '.' || migrationFile == '..') {
          return;
        }
      
        let migration = JSON.parse(jetpack.read(path.resolve(projectNightingaleMigrationsDirectory, migrationFile)));
        migrations[migration.id] = migration;
      });

      // Read all versions for db.json
      let versionDirectories = jetpack.list(projectNightingaleVersionsDirectory)
        , versions = [];
      
      _.each(versionDirectories, function(versionDirectory){
        if (versionDirectory == '.' || versionDirectory == '..') {
          return;
        }

        version = {
          id: parseInt(versionDirectory),
          migrations: {}
        };
        
        let migrationFiles = jetpack.list(path.resolve(projectNightingaleVersionsDirectory, versionDirectory))
          , migrations = {};
        
        _.each(migrationFiles, function(migrationFile) {
          if (migrationFile == '.' || migrationFile == '..') {
            return;
          }
        
          let migration = JSON.parse(jetpack.read(path.resolve(projectNightingaleMigrationsDirectory, migrationFile)));
          migrations[migration.id] = migration;
        });

        version.migrations = migrations;

        versions[parseInt(versionDirectory)] = version;
      });

      // Create the project db file
      projectDb.defaults({
        version: -1,
        maxVersion: -1,
        versions: versions,
        migrations: migrations,
      }).write().then(function(){
        self.close();
      });
    },
  },

  afterRender: function(){
    this.testDatabaseModal = TestDatabaseModalView;
    this.db = lowdb(dbFile, {
      storage: fileAsync
    });

    $('#version-type').bootstrapToggle();

    // $.validator.addMethod("AbsolutePath", function (value,element) {
    //   return this.optional(element) || /^[^\.]/.test( value );
    // },"Please enter an absolute path.");
    // 
    // $.validator.addMethod("PathExists", function (value,element) {
    //   return this.optional(element) || fs.existsSync(path.resolve(value));
    // },"Please enter a valid path.");
    // 
    // $('#add-project-form').validate({
    //   onfocusout: true,
    //   rules: {
    //     path: {
    //       required: true,
    //       AbsolutePath: true,
    //       PathExists: true
    //     },
    //     name: {
    //       required: true,
    //     },
    //     domain: {
    //       required: true,
    //     },
    //     host: {
    //       required: true,
    //     },
    //     user: {
    //       required: true,
    //     },
    //     password: {
    //       required: true,
    //     },
    //     database: {
    //       required: true,
    //     },
    //   },
    // });
  },
});

export default AddProjectModalView;