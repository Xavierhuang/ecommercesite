<?php
define('DISPLAY_ERRORS', true);

// HTTP
define('HTTP_SERVER', 'http://localhost:8000/admin/');
define('HTTP_CATALOG', 'http://localhost:8000/');

// HTTPS
define('HTTPS_SERVER', 'http://localhost:8000/admin/');
define('HTTPS_CATALOG', 'http://localhost:8000/');

// DIR - Local paths (use forward slashes)
$base = dirname(dirname(__FILE__)) . '/';
define('DIR_APPLICATION', $base . 'admin/');
define('DIR_SYSTEM', $base . 'system/');
define('DIR_IMAGE', $base . 'image/');
define('DIR_STORAGE', $base . 'storage/');
define('DIR_CATALOG', $base . 'catalog/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB - Local database (update credentials below)
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', '127.0.0.1');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'dev_oc_4566');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');

// OpenCart API
define('OPENCART_SERVER', 'https://www.opencart.com/');
