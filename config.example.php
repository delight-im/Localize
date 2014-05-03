<?php

define('CONFIG_DB_CONNECT_STRING', 'mysql:host=localhost;dbname=localize'); // PDO database connect string
define('CONFIG_DB_USERNAME', 'root'); // username for database authorization
define('CONFIG_DB_PASSWORD', ''); // password for database authorization
define('CONFIG_DB_REPOSITORIES_SEQUENCE', NULL); // needed on same DB systems (e.g. Postgres) for getLastInsertID()

define('CONFIG_ROOT_URL', 'http://www.localize.im/'); // public base URL to the site (location of this folder) which must use <https://> if you want to use SSL/TLS and which must always end with a trailing slash
define('CONFIG_SITE_NAME', 'Localize'); // public site name
define('CONFIG_SITE_EMAIL', 'info@example.org'); // public email address of site (used as sender of mails)
define('CONFIG_BASE_PATH', '/path/to/this/file/'); // absolute local base path to the directory of this file (with trailing slash)
define('CONFIG_URL_REWRITE', true); // whether to enable URL rewriting (true) or not (false)
define('CONFIG_ERROR_REPORTING_ON', true); // whether error reporting on display is enabled (true) or not (false) which it should only be for debugging
define('CONFIG_ALLOW_SIGN_UP_DEVELOPERS', true); // whether developers may sign up as well to host their own projects (true) or only translators (false)
define('CONFIG_FORCE_SSL', false); // whether to force SSL (HTTPS) for session cookies and HTTP Strict Transport Security (HSTS) or not
define('CONFIG_ASSETS_CDN', ''); // whether to use an external content delivery network for CSS and JS files (full URL to directory containing CSS and JS directories, with trailing slash) or not (empty string)

define('CONFIG_TEMP_PATH', 'temp/'); // local directory where temporary files (e.g. during export) will be saved (with trailing slash)
define('CONFIG_UPLOAD_PATH', 'uploads/'); // local directory where uploaded files will be saved (with trailing slash)
define('CONFIG_MAX_FILE_SIZE', 1572864); // 1024 * 1024 * 1.5

?>