/***
 *  Exposes
 *    Version.MigrationComplete
 *  Consumes
 */
let $ = require('jquery');
let _ = require('underscore');
let Backbone = require('backbone');
import lowdb from 'lowdb';
import path from 'path';
import { remote } from 'electron';
import jetpack from 'fs-jetpack';
import mysql from 'mysql';

const app = remote.app;
const dbFile = path.join(app.getPath('userData'), 'db.json');
const fileSync = require('lowdb/lib/storages/file-sync');

var Schema = Backbone.Model.extend({
  projectId: null,

  defaults: {
    name: null,
    sql: null,
    onDisk: false,
    onDb: false
  },

  sync: function(method, model, options) {
    this.projectId = parseInt(options.projectId);
    let db = lowdb(dbFile, { storage: fileSync })
      , nProject = db.get('projects').find({id: parseInt(options.projectId) }).value()
      , projectDb = lowdb(path.resolve(nProject.path, 'nightingale', 'meta', 'db.json'), { storage: fileSync });
    if (method == 'read') {
      let version = projectDb.get('versions').find({ id: parseInt(model.get('id')) }).value();
      options.success(version);
    }
    else {
      options.success();
    }
  },
  parse: function(response) {
    return {
      id: response.id,
      migrations: response.migrations
    };
  },

  migrateTo: function() {
    let self = this
      , db = lowdb(dbFile, { storage: fileSync })
      , nProject = db.get('projects').find({id: this.projectId }).value()
      , projectDb = lowdb(path.resolve(nProject.path, 'nightingale', 'meta', 'db.json'), { storage: fileSync })
      , project = projectDb.getState()
      , version
      , projectVersion = project.version
      , con = mysql.createConnection({
        host: nProject.sqlHost,
        user: nProject.sqlUser,
        password: nProject.sqlPassword,
        database: nProject.database,
        multipleStatements: true
      });

    if (project.version == this.get('id')) {
      return true;
    }

    console.info('Migrate from ' + projectVersion + ' to ' + this.get('id'));

    con.connect(function(err){
      if (err) {
        console.log(err);
        throw err;
      }
      else {
        // Migrate up
        if (projectVersion <= self.get('id')) {
          projectVersion++;
          while (projectVersion <= parseInt(self.get('id'))) {
            version = new Version(project.versions[projectVersion]);
            version.projectId = self.projectId;
            project = version.migrateUp(con);
            projectDb
              .setState(project);
            projectVersion++;
          }
        }
        // Migrate down
        else {
          // Migrate down unversioned migrations
          _.each(project.migrations, function(migrationJSON){
            let migration = new Migration(migrationJSON);
            migration.projectId = self.projectId;
            project = migration.migrateDown(con);
            projectDb
              .setState(project);
          });

          // Migrate down versions
          while (projectVersion > self.get('id')) {
            version = new Version(project.versions[projectVersion]);
            version.projectId = self.projectId;
            project = version.migrateDown(con);
            projectDb
              .setState(project);
            projectVersion--;
          }
        }
      }

      con.end();
      console.info('Migration complete');
      $('body').trigger('Version.MigrationComplete')
    });
  },

  migrateUp: function(con) {
    let self = this
      , db = lowdb(dbFile, { storage: fileSync })
      , nProject = db.get('projects').find({id: this.projectId }).value()
      , projectDb = lowdb(path.resolve(nProject.path, 'nightingale', 'meta', 'db.json'), { storage: fileSync })
      , project = projectDb.getState();

      console.info('Migrate up version ' + this.get('id'));
      console.info(_.size(this.get('migrations')) + ' migrations');

      _.each(this.get('migrations'), function(migrationJSON){
        let migration = new Migration(migrationJSON);
        migration.projectId = self.projectId;
        project = migration.migrateUp(con);
      });
      
      project.version = parseInt(this.get('id'));
      
      console.info('Version ' + this.get('id') + ' migrated up.');
      return project;
  },

  migrateDown: function(con) {
    let self = this
      , db = lowdb(dbFile, { storage: fileSync })
      , nProject = db.get('projects').find({id: this.projectId }).value()
      , projectDb = lowdb(path.resolve(nProject.path, 'nightingale', 'meta', 'db.json'), { storage: fileSync })
      , project = projectDb.getState();
      
      console.info('Migrate down version ' + this.get('id'));
      console.info(_.size(this.get('migrations')) + ' migrations');

      _.each(this.get('migrations'), function(migrationJSON){
        let migration = new Migration(migrationJSON);
        migration.projectId = self.projectId;
        project = migration.migrateDown(con);
      });
      
      project.version = parseInt(this.get('id'))-1;
      
      console.info('Version ' + this.get('id') + ' migrated down.');
      return project;
  },
});

export default Schema;