# Nightingale

A different kind of database version control.

The database version control software I've seen all rely on sequential versions created one at a time. This works fine for standard development work where developers require the most recent version of the database at all times. However, it is not always the case that the most recent database version is required. The case I run into most frequently is hotfixes. If the version of the development database has progressed from 5, the current production version, to 10 and a hotfix needs to be created then it is version 11. This creates a problem for production which has to run the migration code from version 11 when the hotfix is deployed but not run it once the standard development changes are deployed. The solution to this I have seen is to run hotfix migrations by hand introducing human error into a rigorous standardized system.

Nightingale is different by keeping migrations and revisions separate until deployment. Nightingale began with these starting principles:
* A database has Revisions
* Database revisions are sequential integers
* A database has Environments that it is deployed to
* Database Revisions are made up of Migrations
* A Migration happens within a Revision
* A Migration may follow another Migration
* A Migration has a name
* A Migration is made up of migration SQL
* Migration SQL contains an up and down set

In Nightingale you begin by creating Migrations. Migrations are shared with other developers and any shared development environments. If you current development environment requires a certain set of Migrations you are fully capable of running your set of Migrations up or down as required.

When it comes time to create a Revision a subset of the possible Migrations are selected. Revisions are not finalized until they are pushed and shared with everyone. Once a Revision is pushed a new sequential integer is set for it and it is added to the list of Revisions. This allows any developer to create a Revision at any time regardless of the migrations created by others.

## Installation

Begin by creating a bare repository. Then clone the bare repository onto all relevant environments running your database, into a directory that is web accessible. Ensure that the repository is set as a shared repository and that the user accessing the files through the browser has proper access to commit, push, pull, etc. Copy Nightingale's core files into one of the repositories and distribute it to the environments. Set up each environments config.php and local_config.php files. Begin creating Migrations and Revisions.

### Stuff used to make this:

 * [bootstrap](http://getbootstrap.com/)
 * [bootstrap-material-design](https://github.com/FezVrasta/bootstrap-material-design)
 * [dataTables](https://datatables.net/)
