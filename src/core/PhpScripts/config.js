let script = `<?php
/**
 * Your database authentication information goes here
 */
define('DB_HOST', '<%= sqlHost %>');
define('DB_PORT', <%= sqlPort %>);
define('DB_USERNAME', '<%= sqlUser %>');
define('DB_PASSWORD', '<%= sqlPassword %>');
define('DB_NAME', '<%= database %>');
define('SSH_HOST', <%= requireSsh ? "'"+sshHost+"'" : false %>);
define('SSH_PORT', <%= requireSsh ? "'"+sshPort+"'" : false %>);
define('SSH_USER', <%= requireSsh ? "'"+sshUser+"'" : false %>);
define('SSH_PRIVATE_KEY', <%= requireSsh ? "'"+sshPrivateKey+"'" : false %>);`;

export { script };