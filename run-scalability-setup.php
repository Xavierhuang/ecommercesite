#!/usr/bin/env php
<?php
/**
 * Scalability setup script. Run from project root: php run-scalability-setup.php [--redis]
 *
 * Without --redis: adds session table index only.
 * With --redis: also enables Redis cache in config (uncomments CACHE_* in config.php, sets cache_engine in system/config/default.php).
 *
 * Requirements: DB credentials in config.php. For --redis: Redis server running; PHP redis extension installed.
 */

$usage = "Usage: php run-scalability-setup.php [--redis]\n  --redis  Also enable Redis cache in config (requires Redis and php-redis).\n";

$enable_redis = in_array('--redis', $argv ?? [], true);

if (!defined('DIR_APPLICATION')) {
    if (!is_file(__DIR__ . '/config.php')) {
        fwrite(STDERR, "Run from project root (where config.php is).\n");
        exit(1);
    }
    require __DIR__ . '/config.php';
}

$db_host = defined('DB_HOSTNAME') ? DB_HOSTNAME : '127.0.0.1';
$db_user = defined('DB_USERNAME') ? DB_USERNAME : 'root';
$db_pass = defined('DB_PASSWORD') ? DB_PASSWORD : '';
$db_name = defined('DB_DATABASE') ? DB_DATABASE : '';
$db_port = defined('DB_PORT') ? DB_PORT : 3306;
$prefix   = defined('DB_PREFIX') ? DB_PREFIX : 'oc_';

$table = $prefix . 'session';

echo "Scalability setup\n";
echo "----------------\n";

// ----- 1. Session index -----
$mysqli = @new mysqli($db_host, $db_user, $db_pass, $db_name, (int)$db_port);
if ($mysqli->connect_error) {
    fwrite(STDERR, "DB connect failed: " . $mysqli->connect_error . "\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$idx_exists = false;
$res = $mysqli->query("SHOW INDEX FROM `" . $mysqli->real_escape_string($table) . "` WHERE Key_name = 'idx_expire'");
if ($res && $res->num_rows > 0) {
    $idx_exists = true;
}
if ($res) {
    $res->close();
}

if ($idx_exists) {
    echo "[OK] Session table already has index idx_expire.\n";
} else {
    $sql = "ALTER TABLE `" . $mysqli->real_escape_string($table) . "` ADD INDEX idx_expire (expire)";
    if ($mysqli->query($sql)) {
        echo "[OK] Added index idx_expire on {$table}.\n";
    } else {
        fwrite(STDERR, "[FAIL] Session index: " . $mysqli->error . "\n");
        $mysqli->close();
        exit(1);
    }
}
$mysqli->close();

// ----- 2. Optional: enable Redis -----
if (!$enable_redis) {
    echo "Done. To enable Redis cache, run: php run-scalability-setup.php --redis\n";
    exit(0);
}

echo "\nEnabling Redis cache in config...\n";

$config_php = __DIR__ . '/config.php';
$default_php = defined('DIR_CONFIG') ? DIR_CONFIG . 'default.php' : __DIR__ . '/system/config/default.php';

if (!is_readable($config_php) || !is_readable($default_php)) {
    fwrite(STDERR, "[FAIL] Cannot read config.php or system/config/default.php.\n");
    exit(1);
}

$config_content = file_get_contents($config_php);

// Uncomment CACHE_* lines
$config_content = str_replace("// define('CACHE_HOSTNAME', '127.0.0.1');", "define('CACHE_HOSTNAME', '127.0.0.1');", $config_content);
$config_content = str_replace("// define('CACHE_PORT', 6379);       // Redis default; use 11211 for Memcached", "define('CACHE_PORT', 6379);", $config_content);
$config_content = str_replace("// define('CACHE_PREFIX', 'oc_');   // optional, to avoid key clashes", "define('CACHE_PREFIX', 'oc_');", $config_content);
if (file_put_contents($config_php, $config_content) === false) {
    fwrite(STDERR, "[FAIL] Could not write config.php.\n");
    exit(1);
}
echo "[OK] config.php: CACHE_HOSTNAME, CACHE_PORT, CACHE_PREFIX enabled.\n";

$default_content = file_get_contents($default_php);
$default_content = preg_replace(
    "/\$_\['cache_engine'\]\s*=\s*'[^']*';/",
    "\$_['cache_engine']         = 'redis';",
    $default_content,
    1
);
if (file_put_contents($default_php, $default_content) === false) {
    fwrite(STDERR, "[FAIL] Could not write system/config/default.php.\n");
    exit(1);
}
echo "[OK] system/config/default.php: cache_engine set to redis.\n";

// Quick check: Redis extension and connection
if (!extension_loaded('redis')) {
    echo "[WARN] PHP Redis extension not loaded. Install it (e.g. pecl install redis or php-redis) and restart PHP.\n";
    exit(0);
}
$host = '127.0.0.1';
$port = 6379;
if (defined('CACHE_HOSTNAME')) {
    $host = CACHE_HOSTNAME;
}
if (defined('CACHE_PORT')) {
    $port = (int) CACHE_PORT;
}
$redis = @new Redis();
if (@$redis->connect($host, (int)$port, 2)) {
    $redis->close();
    echo "[OK] Redis connection to {$host}:{$port} succeeded.\n";
} else {
    echo "[WARN] Could not connect to Redis at {$host}:{$port}. Start Redis or fix CACHE_HOSTNAME/CACHE_PORT in config.php.\n";
}

echo "\nDone. Clear storage/cache if you had file cache before, then load the site.\n";
