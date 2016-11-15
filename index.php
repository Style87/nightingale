<?php

set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }

    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'local_config.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib/functions.php';
// Models
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib/Logger.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib/Migration.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib/Revision.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib/LocalRevision.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib/Git.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib/Schema.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib/SchemaTable.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib/SchemaView.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib/SchemaTrigger.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib/SchemaProcedure.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib/SchemaFunction.php';

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib/NightingaleDatabase.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Router.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Nightingale.php';
// Controllers
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DashboardController.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'MigrationsController.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'RevisionsController.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'GitController.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'SqliteController.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'SchemaController.php';

Nightingale::authenticate();

$router = Router::instance();
$router->dispatch();

/*
$nightingale = Nightingale::instance();
$nightingale->authenticate();
$nightingale->dispatch();
*/
