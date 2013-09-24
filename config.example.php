<?php
define('CONFIG_DB_CONNECT_STRING', 'mysql:host=localhost;dbname=localize'); // PDO database connect string
define('CONFIG_DB_USERNAME', 'root'); // username for database authorization
define('CONFIG_DB_PASSWORD', ''); // password for database authorization
define('CONFIG_DB_REPOSITORIES_SEQUENCE', NULL); // needed on same DB systems (e.g. Postgres) for getLastInsertID()
define('CONFIG_ALLOW_SIGN_UP_DEVELOPERS', true); // whether developers may sign up as well to host their own projects (true) or only translators (false)
define('CONFIG_TEMP_FOLDER', 'temp/'); // local directory where temporary files (e.g. during export) will be saved
define('CONFIG_UPLOAD_FOLDER', 'uploads/'); // local directory where uploaded files will be saved
define('CONFIG_MAX_FILE_SIZE', 1572864); // 1024 * 1024 * 1.5
define('CONFIG_ROOT_URL', 'http://localhost/localize/'); // root URL with trailing slash
define('CONFIG_SESSION_HTTPS', false); // whether to use only HTTPS (true) or not (false) for sessions
define('CONFIG_ERROR_REPORTING_ON', true); // whether error reporting on display is enabled (true) or not (false) which it should only be for debugging
define('CONFIG_URL_REWRITE', true); // whether to enable URL rewriting (true) or not (false)
?>