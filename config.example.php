<?php

define('CONFIG_DB_CONNECT_STRING', 'mysql:host=localhost;dbname=localize'); // PDO database connect string
define('CONFIG_DB_USERNAME', 'root'); // username for database authorization
define('CONFIG_DB_PASSWORD', ''); // password for database authorization
define('CONFIG_DB_REPOSITORIES_SEQUENCE', NULL); // needed on same DB systems (e.g. Postgres) for getLastInsertID()

define('CONFIG_ROOT_URL', 'http://www.localize.io/'); // public base URL to this folder (with trailing slash)
define('CONFIG_BASE_PATH', '/path/to/this/file/'); // absolute local base path to the directory of this file (with trailing slash)
define('CONFIG_URL_REWRITE', true); // whether to enable URL rewriting (true) or not (false)
define('CONFIG_ERROR_REPORTING_ON', true); // whether error reporting on display is enabled (true) or not (false) which it should only be for debugging
define('CONFIG_ALLOW_SIGN_UP_DEVELOPERS', true); // whether developers may sign up as well to host their own projects (true) or only translators (false)
define('CONFIG_SESSION_HTTPS', false); // whether to use only HTTPS (true) or not (false) for sessions

define('CONFIG_TEMP_PATH', 'temp/'); // local directory where temporary files (e.g. during export) will be saved (with trailing slash)
define('CONFIG_UPLOAD_PATH', 'uploads/'); // local directory where uploaded files will be saved (with trailing slash)
define('CONFIG_MAX_FILE_SIZE', 1572864); // 1024 * 1024 * 1.5

define('CONFIG_PAYMENTS_FLATTR_DATA', ''); // your personal Flattr.com account data: 'user_id=[USERNAME]&url=[URL]&title=[TITLE]&hidden=[HIDDEN]&category=[CATEGORY]' or an empty string

?>