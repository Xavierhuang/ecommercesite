<?php 
define('HTTP_SERVER', 'http://diversiply.test/');

// HTTPS
define('HTTPS_SERVER', 'http://diversiply.test/');

// DIR - Local paths (use forward slashes)
define('DIR_APPLICATION', 'F:/PROJECTS/WORK/Lockwood/2026/feb/dev.diversiply.co/catalog/');
define('DIR_SYSTEM', 'F:/PROJECTS/WORK/Lockwood/2026/feb/dev.diversiply.co/system/');
define('DIR_IMAGE', 'F:/PROJECTS/WORK/Lockwood/2026/feb/dev.diversiply.co/image/');
define('DIR_STORAGE', 'F:/PROJECTS/WORK/Lockwood/2026/feb/dev.diversiply.co/storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
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
define('DB_PASSWORD', 'root');
define('DB_DATABASE', 'dev_oc_4566');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');
