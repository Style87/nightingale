let $ = require('jquery');
let _ = require('underscore');
let Backbone = require('backbone');
import lowdb from 'lowdb';
import { uuid } from '../core/uuid.js';
import path from 'path';
import { remote } from 'electron';
import jetpack from 'fs-jetpack';

const app = remote.app;
const dbFile = path.join(app.getPath('userData'), 'db.json');
const fileSync = require('lowdb/lib/storages/file-sync');

var Migration = Backbone.Model.extend({
    idAttribute: 'id',

    projectId: null,

    defaults: {
      id:null,
      description:'',
      sqlUp:'',
      sqlDown:'',
      parentMigrationId:null,
      hasRun: false,
      version: null,
    },

    sync: function(method, model, options) {
      this.projectId = parseInt(options.projectId);
      let db = lowdb(dbFile, { storage: fileSync })
        , nProject = db.get('projects').find({id: parseInt(options.projectId)}).value()
        , projectDb = lowdb(path.resolve(nProject.path, 'nightingale', 'meta', 'db.json'), { storage: fileSync });

      if (method == 'read') {
        if (options.versionId) {
          var version = projectDb.get('versions').find({
            id: options.versionId
          }).value()
          , migration = version.migrations[model.id];
        }
        else {
          var migration = projectDb.get('migrations').find({
            id: model.id
          }).value();
        }
        options.success(migration);
      }
      else {
        let project = projectDb.getState();
        if (method == 'create') {
          model.set({id: uuid()})
          project.migrations[this.id] = model.attributes;
        }
        else if (method == 'update') {
          project.migrations[model.id] = model.attributes;
        }
        else if (method == 'delete') {
          delete project.migrations[model.id];
          let filename = path.resolve(nProject.path, 'nightingale', 'migrations', model.get('id')+'.json');
          jetpack.remove(filename);
        }

        if (method == 'create' || method == 'update') {
          let filename = path.resolve(nProject.path, 'nightingale', 'migrations', model.get('id')+'.json')
            , json = model.toJSON();
          delete json.hasRun;
          jetpack.file(filename);
          jetpack.write(filename, JSON.stringify(json));
        }

        projectDb.write();
        options.success();
      }
    },

    migrateUp: function(con) {
      console.info('Migrate up ' + this.get('id'));
      let self = this
        , db = lowdb(dbFile, { storage: fileSync })
        , nProject = db.get('projects').find({id: parseInt(this.projectId) }).value()
        , projectDb = lowdb(path.resolve(nProject.path, 'nightingale', 'meta', 'db.json'), { storage: fileSync })
        , project = projectDb.getState();

        if (this.get('hasRun') == true) {
          console.info('Migration ' + this.get('id') + ' has already run');
          return project;
        }

        // Migrate up the parent migration
        if (this.get('parentMigrationId') != null) {
          console.log('Check parent migration ' + this.get('parentMigrationId'));
          let parentMigration = new Migration({id: this.get('parentMigrationId')});
          parentMigration.fetch({
            projectId: this.projectId,
            versionId: this.get('version')
          });

          project = parentMigration.migrateUp(con);
        }

        console.info('Migration ' + this.get('id'));

        console.info('Execute: ' + this.get('sqlUp'));
        con.query(this.get('sqlUp'));

        if (this.get('version') == null) {
          project.migrations[this.get('id')].hasRun = true;
        }
        else {
          project.versions[this.get('version')].migrations[this.get('id')].hasRun = true;
        }

        projectDb
          .setState(project);
        console.info('Migration ' + this.get('id') + ' migrated up.');
        return project;
    },
    
    migrateDown: function(con) {
      console.info('Migrate down ' + this.get('id'));
      let self = this
        , db = lowdb(dbFile, { storage: fileSync })
        , nProject = db.get('projects').find({id: parseInt(this.projectId) }).value()
        , projectDb = lowdb(path.resolve(nProject.path, 'nightingale', 'meta', 'db.json'), { storage: fileSync })
        , project = projectDb.getState()
        , childMigrations;

        if (this.get('hasRun') == false) {
          console.info('Migration ' + this.get('id') + ' has not run');
          return project;
        }

        // Migrate down child migrations
        console.log('Check child migrations');
        if (this.get('version') != null) {
          childMigrations = projectDb.get('versions').find({id: this.get('version')}).filter({parentMigrationId: this.get('id')}).value();
        }
        else {
          childMigrations = projectDb.get('migrations').filter({parentMigrationId: this.get('id')}).value();
        }

        _.each(childMigrations, function(migration){
          let childMigration = new Migration(migration);
          childMigration.fetch({
            projectId: self.projectId,
            versionId: self.get('version')
          });
          project = childMigration.migrateDown(con);
        });

        console.info('Migration ' + this.get('id'));

        console.info('Execute: ' + this.get('sqlDown'));
        con.query(this.get('sqlDown'));
        
        if (this.get('version') == null) {
          project.migrations[this.get('id')].hasRun = false;
        }
        else {
          project.versions[this.get('version')].migrations[this.get('id')].hasRun = false;
        }
        
        projectDb
          .write();
        console.info('Migration ' + this.get('id') + ' migrated down.');
        return project;
    },
});

export default Migration;