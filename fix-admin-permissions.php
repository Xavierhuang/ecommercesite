<?php
/**
 * Grant full access/modify permissions to admin user group 1 (Administrator).
 * Use when you get "Permission Denied" after login.
 * Usage: php fix-admin-permissions.php
 */

$admin_config = __DIR__ . '/admin/config.php';
if (!is_file($admin_config)) {
    die("admin/config.php not found.\n");
}
require_once $admin_config;

// Only ignore routes that startup/permission.php ignores (so we allow all others)
$ignore = array(
    'common/dashboard',
    'common/login',
    'common/logout',
    'common/forgotten',
    'common/reset',
    'error/not_found',
    'error/permission'
);

$controller_dir = DIR_APPLICATION . 'controller/';
$path = array($controller_dir . '*');
$routes = array();

while (count($path) != 0) {
    $next = array_shift($path);
    foreach (glob($next) as $file) {
        if (is_dir($file)) {
            $path[] = $file . '/*';
        } elseif (is_file($file)) {
            $controller = substr($file, strlen($controller_dir));
            $permission = substr($controller, 0, strrpos($controller, '.'));
            $permission = strtolower($permission);
            if (!in_array($permission, $ignore)) {
                $routes[] = $permission;
            }
        }
    }
}

$routes = array_unique($routes);
sort($routes);
$permission = array('access' => $routes, 'modify' => $routes);
$json = json_encode($permission);

$prefix = defined('DB_PREFIX') ? DB_PREFIX : 'oc_';
$conn = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, defined('DB_PORT') ? DB_PORT : 3306);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error . "\n");
}

$table = $conn->real_escape_string($prefix . 'user_group');
$json_esc = $conn->real_escape_string($json);
$conn->query("UPDATE `{$table}` SET permission = '{$json_esc}' WHERE user_group_id = 1");

if ($conn->error) {
    die("Update failed: " . $conn->error . "\n");
}

echo "User group 1 (Administrator) updated with full access to " . count($routes) . " routes.\n";
echo "Log out and log in again for changes to take effect.\n";
$conn->close();
