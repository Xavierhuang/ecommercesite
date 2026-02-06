<?php
// LOCAL DEVELOPMENT CONFIGURATION - ADMIN
// Copy this to admin/config.php and update with your local settings

define('DISPLAY_ERRORS', true);

// HTTP
define('HTTP_SERVER', 'http://localhost/diversiply/admin/');
define('HTTP_CATALOG', 'http://localhost/diversiply/');

// HTTPS
define('HTTPS_SERVER', 'http://localhost/diversiply/admin/');
define('HTTPS_CATALOG', 'http://localhost/diversiply/');

// DIR - Update these paths to your local installation
define('DIR_APPLICATION', 'F:/PROJECTS/WORK/Lockwood/2026/feb/dev.diversiply.co/admin/');
define('DIR_SYSTEM', 'F:/PROJECTS/WORK/Lockwood/2026/feb/dev.diversiply.co/system/');
define('DIR_IMAGE', 'F:/PROJECTS/WORK/Lockwood/2026/feb/dev.diversiply.co/image/');
define('DIR_STORAGE', 'F:/PROJECTS/WORK/Lockwood/2026/feb/dev.diversiply.co/storage/');
define('DIR_CATALOG', 'F:/PROJECTS/WORK/Lockwood/2026/feb/dev.diversiply.co/catalog/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB - Your local MySQL/MariaDB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'your_password');
define('DB_DATABASE', 'diversiply_local');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');

// OpenCart API
define('OPENCART_SERVER', 'https://www.opencart.com/');
