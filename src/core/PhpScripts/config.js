let script = `<?php
/**
 * Your database authentication information goes here
 */
define('DB_HOST', '<%= host %>');
define('DB_PORT', <%= port %>);
define('DB_USERNAME', '<%= user %>');
define('DB_PASSWORD', '<%= password %>');
define('DB_NAME', '<%= database %>');`;

export { script };