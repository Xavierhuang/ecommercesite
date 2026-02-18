<?php
define('HTTP_SERVER', 'https://tkurydyfjkv.diversiply.co/');
define('HTTPS_SERVER', 'https://tkurydyfjkv.diversiply.co/');

// DIR - dev-local storage (inside tkurydyfjkv folder)
$base = dirname(__FILE__) . '/';
define('DIR_APPLICATION', $base . 'catalog/');
define('DIR_SYSTEM', $base . 'system/');
define('DIR_IMAGE', $base . 'image/');
define('DIR_STORAGE', $base . 'storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB - DreamHost MySQL (primary: bossingup; fallback: mysql.diversiply.co if configured)
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'tkurydyfjkvoc.bossingup.dreamhosters.com');
// define('DB_HOSTNAME', 'mysql.diversiply.co'); // alternate if primary fails
define('DB_USERNAME', 'tkurydyfjkv_oc');
define('DB_PASSWORD', 'Hhwj65377068');
define('DB_DATABASE', 'tkurydyfjkv_oc');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');
