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
import { script as ConfigScript} from '../core/PhpScripts/config.js';

import { script as postCheckoutHookScript } from '../core/PostCheckoutHookScript.js';
import { script as GitIgnore } from '../core/GitIgnore.js';
import { template as AddProjectModalHeaderTemplate } from '../templates/AddProjectModalHeaderTemplate.js';
import { template as AddProjectModalBodyTemplate } from '../templates/AddProjectModalBodyTemplate.js';
import { template as AddProjectModalFooterTemplate } from '../templates/AddProjectModalFooterTemplate.js';
import TestDatabaseModalView from './TestDatabaseModalView.js';
import lowdb from 'lowdb';
import jetpack from 'fs-jetpack';
import path from 'path';
import { remote, ipcRenderer } from 'electron';
import mysql from 'mysql2';
import async from 'async';

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
    'click #mysql-connection-type .toggle' : function(e) {
      if ($(e.currentTarget).hasClass('off')) {
        $('#mysql-ssh-content').hide();
      }
      else {
        $('#mysql-ssh-content').show();
      }
    },
    'click #test-database-btn' : function(e) {
      e.preventDefault();
      e.stopPropagation();
      var self = this
        , $button = $(e.currentTarget)
        , sqlConfig = {
          host: $('#add-project-host').val(),
          user: $('#add-project-user').val(),
          password: $('#add-project-password').val(),
          database: $('#add-project-database').val()
        }
        , sshConfig = null;
      
      $button.addClass('disabled').prop('disabled', true);
      if (!$('#mysql-connection-type input').is(':checked')) {
        sshConfig = {
          host: $('#add-project-ssh-host').val(),
          port: $('#add-project-ssh-port').val(),
          user: $('#add-project-ssh-user').val(),
          privateKey: jetpack.read($('#add-project-ssh-private-key').val()),
        };
      }

      var sqlError = function(e, err){
        self.testDatabaseModal.setOptions({
          "class": 'text-danger',
          "text": 'Test failed.',
        });
        self.testDatabaseModal.open();
        $button.removeClass('disabled').prop('disabled', false);
      };

      ipcRenderer.send('sql-connect', sqlConfig, sshConfig)
      
      ipcRenderer.once('sql-connected', function(){
        ipcRenderer.send('sql-end');
      })
      ipcRenderer.once('sql-error', sqlError)
      ipcRenderer.once('sql-end', function(){
        self.testDatabaseModal.setOptions({
          "class": 'text-success',
          "text": 'Test successful.',
        });
        self.testDatabaseModal.open();
        $button.removeClass('disabled').prop('disabled', false);
        ipcRenderer.removeListener('sql-error', sqlError);
      })
    },
    'click #save-project-btn': function(e) {
      e.preventDefault();
      e.stopPropagation();
      $('#save-project-btn, #close-project-btn').addClass('disabled').prop('disabled', true);
      var self = this
        , id = parseInt($('#add-project-id').val()) || Date.now()
        , projectData = {
          id: parseInt($('#add-project-id').val()) || id,
          path: $('#add-project-path').val(),
          name: $('#add-project-name').val(),
          domain: $('#add-project-guest-domain').val(),
          sqlHost: $('#add-project-host').val(),
          sqlUser: $('#add-project-user').val(),
          sqlPassword: $('#add-project-password').val(),
          database: $('#add-project-database').val(),
          requireSsh: !$('#mysql-connection-type input').is(':checked'),
        }
        , project = null;

      if (!$('#mysql-connection-type input').is(':checked')) {
        projectData.sshHost = $('#add-project-ssh-host').val();
        projectData.sshPort = $('#add-project-ssh-port').val();
        projectData.sshUser = $('#add-project-ssh-user').val();
        projectData.sshPrivateKey = $('#add-project-ssh-private-key').val();
      }

      if ((
          projectData.path == '' || projectData.name == '' || projectData.sqlHost == '' ||
          projectData.sqlUser == '' || projectData.sqlPassword == '' || projectData.database == '') || (projectData.requireSsh && (projectData.sshHost == '' || projectData.sshPort == '' || projectData.sshUser == '' || projectData.sshPrivateKey == ''))) {
        var message = ''
          , modal = null;
        
        if (projectData.path == '') {
          message += 'Project <strong class="text-danger">path</strong> cannot be empty.<br>';
        }
        if (projectData.name == '') {
          message += 'Project <strong class="text-danger">name</strong> cannot be empty.<br>';
        }
        if (projectData.sqlHost == '') {
          message += 'Project <strong class="text-danger">sql host</strong> cannot be empty.<br>';
        }
        if (projectData.sqlUser == '') {
          message += 'Project <strong class="text-danger">sql user</strong> cannot be empty.<br>';
        }
        if (projectData.sqlPassword == '') {
          message += 'Project <strong class="text-danger">sql password</strong> cannot be empty.<br>';
        }
        if (projectData.requireSsh) {
          if (projectData.sshHost == '') {
            message += 'Project <strong class="text-danger">ssh host</strong> cannot be empty when using ssh connection method.<br>';
          }
          if (projectData.sshUser == '') {
            message += 'Project <strong class="text-danger">ssh user</strong> cannot be empty when using ssh connection method.<br>';
          }
          if (projectData.sshPort == '') {
            message += 'Project <strong class="text-danger">ssh port</strong> cannot be empty when using ssh connection method.<br>';
          }
          if (projectData.sshPrivateKey == '') {
            message += 'Project <strong class="text-danger">ssh private key</strong> cannot be empty when using ssh connection method.<br>';
          }
        }

        modal = new BaseModalView({
          title: 'Error',
          body: message,
          showAffirmButton: false,
          afterRender: function() {
            let self = this;
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
      $('#save-project-btn, #close-project-btn').addClass('disabled').prop('disabled', true);

      if (!$('#add-project-id').val())
      {
        project = this.db.get('projects').find({path:$('#add-project-path').val()}).value();
    
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
      }

      if ($('#add-project-id').val())
      {
        this.db
          .get('projects')
          .find({id:parseInt($('#add-project-id').val())})
          .assign(projectData)
          .write()
          .then(function(){
            self.close();
          });
      }
      else
      {
        this.db
          .get('projects')
          .push(projectData)
          .write();

        project = projectData;

        var projectNightingaleDirectory = path.resolve(project.path, 'nightingale');
        var projectNightingaleMetaDirectory = path.resolve(projectNightingaleDirectory, 'meta');
        var projectNightingaleMetaSchemaDirectory = path.resolve(projectNightingaleDirectory, 'meta', 'schema');
        var projectNightingaleVersionsDirectory = path.resolve(projectNightingaleDirectory, 'versions');
        var projectNightingaleVersionsReadmeFile = path.resolve(projectNightingaleVersionsDirectory, 'Readme.md');
        var projectNightingaleMigrationsDirectory = path.resolve(projectNightingaleDirectory, 'migrations');
        var projectNightingaleMigrationsReadmeFile = path.resolve(projectNightingaleMigrationsDirectory, 'Readme.md');
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
        jetpack.dir(projectNightingaleMetaSchemaDirectory);
        jetpack.dir(projectNightingaleVersionsDirectory);
        jetpack.dir(projectNightingaleMigrationsDirectory);

        // Create the Readme files
        jetpack.file(projectNightingaleVersionsReadmeFile);
        jetpack.file(projectNightingaleMigrationsReadmeFile);

        let projectDb = lowdb(path.join(projectNightingaleMetaDirectory, 'db.json'), {
          storage: fileAsync
        });

        // Create the php scripts
        jetpack.file(projectNightingaleAdapterMySqlPhpFile);
        jetpack.write(projectNightingaleAdapterMySqlPhpFile, AdapterMySqlScript);
        
        jetpack.file(projectNightingaleConfigPhpFile);
        let configScript = _.template(ConfigScript);
        var configData = projectData;
        configData.sqlPort = 3306;
        jetpack.write(projectNightingaleConfigPhpFile, configScript(configData));
        
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
          if (migrationFile == '.' || migrationFile == '..' || migrationFile == 'Readme.md') {
            return;
          }
        
          let migration = JSON.parse(jetpack.read(path.resolve(projectNightingaleMigrationsDirectory, migrationFile)));
          migrations[migration.id] = migration;
        });

        // Read all versions for db.json
        let versionDirectories = jetpack.list(projectNightingaleVersionsDirectory)
          , versions = [];
        
        _.each(versionDirectories, function(versionDirectory){
          if (versionDirectory == '.' || versionDirectory == '..' || versionDirectory == 'Readme.md') {
            return;
          }

          version = {
            id: parseInt(versionDirectory),
            migrations: {}
          };
          
          let migrationFiles = jetpack.list(path.resolve(projectNightingaleVersionsDirectory, versionDirectory))
            , migrations = {};
          
          _.each(migrationFiles, function(migrationFile) {
            if (migrationFile == '.' || migrationFile == '..' || migrationFile == 'Readme.md') {
              return;
            }
          
            let migration = JSON.parse(jetpack.read(path.resolve(projectNightingaleMigrationsDirectory, migrationFile)));
            migrations[migration.id] = migration;
          });

          version.migrations = migrations;

          versions[parseInt(versionDirectory)] = version;
        });

        // Export the initial schema
        var con = mysql.createConnection({
          host: projectData.sqlHost,
          user: projectData.sqlUser,
          password: projectData.sqlPassword,
          database: projectData.database,
          multipleStatements: true
        });

        con.connect(function(err){
          if (err) {
            console.log(err);
            throw err;
          }

          async.parallel([
              async function(){
                await con.query('SHOW FULL TABLES', async function(err, tableResults, fields) {
                  _.each(tableResults, async function(tableResult, index){
                    await con.query('SHOW CREATE table ' + tableResult.Tables_in_ping_dev + ';', function(err, createTableResults, fields){
                      _.each(createTableResults, function(createTableResult, index){
                        let key = null
                          , file = null
                          , content = null;
                        if ('View' in createTableResult) {
                          key = 'Create View';
                          file = path.resolve(projectNightingaleMetaSchemaDirectory,createTableResult.View+'.sql');
                          content = createTableResult[key];
                        }
                        else {
                          // MySQL's SHOW CREATE TABLE command also includes the AUTO_INCREMENT value, so we're removing it here
                          key = 'Create Table';
                          file = path.resolve(projectNightingaleMetaSchemaDirectory,createTableResult.Table+'.sql');
                          content = createTableResult[key].replace(/\s?AUTO_INCREMENT=\d+\s?/, " ");
                        }
                        jetpack.write(file, content);
                      })
                    });
                  });
                });
              },
          ], function (err, result) {
            // Create the project db file and close the modal
            projectDb.defaults({
              version: -1,
              maxVersion: -1,
              versions: versions,
              migrations: migrations,
            }).write().then(function(){
              self.close();
            });
          });
        });
      }
    },
  },

  afterRender: function(){
    this.testDatabaseModal = TestDatabaseModalView;
    this.db = lowdb(dbFile, {
      storage: fileAsync
    });

    $('#version-type').bootstrapToggle();
    $('#mysql-connection-type input').bootstrapToggle();
  },
});

export default AddProjectModalView;