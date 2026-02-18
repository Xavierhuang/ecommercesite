<?php
/**
 * Reset admin password (or set for first time). Uses same DB as admin/config.php.
 * Usage: php reset-admin-password.php [username] [password]
 * Default: username=newadmin, password=admin123
 */

$admin_config = __DIR__ . '/admin/config.php';
if (!is_file($admin_config)) {
    die("admin/config.php not found.\n");
}
require_once $admin_config;

$username = $argv[1] ?? 'newadmin';
$password = $argv[2] ?? 'admin123';

$prefix = defined('DB_PREFIX') ? DB_PREFIX : 'oc_';
$conn = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, defined('DB_PORT') ? DB_PORT : 3306);

if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error . "\n");
}

$table = $conn->real_escape_string($prefix . 'user');
$user_esc = $conn->real_escape_string($username);

$res = $conn->query("SELECT user_id, username FROM `{$table}` WHERE username = '{$user_esc}' LIMIT 1");
if (!$res) {
    die("Query failed: " . $conn->error . "\n");
}

$salt = substr(bin2hex(random_bytes(5)), 0, 9);
$hash = sha1($salt . sha1($salt . sha1($password)));

if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $uid = (int) $row['user_id'];
    $salt_esc = $conn->real_escape_string($salt);
    $hash_esc = $conn->real_escape_string($hash);
    $conn->query("UPDATE `{$table}` SET salt = '{$salt_esc}', password = '{$hash_esc}', code = '' WHERE user_id = {$uid}");
    if ($conn->error) {
        die("Update failed: " . $conn->error . "\n");
    }
    echo "Password updated for user: {$username}\n";
} else {
    $salt_esc = $conn->real_escape_string($salt);
    $hash_esc = $conn->real_escape_string($hash);
    $email_esc = $conn->real_escape_string($username . '@local.test');
    $fn_esc = $conn->real_escape_string('Admin');
    $ln_esc = $conn->real_escape_string('User');
    $conn->query("INSERT INTO `{$table}` (user_group_id, username, password, salt, firstname, lastname, email, image, code, ip, status, date_added) VALUES (1, '{$user_esc}', '{$hash_esc}', '{$salt_esc}', '{$fn_esc}', '{$ln_esc}', '{$email_esc}', '', '', '', 1, NOW())");
    if ($conn->error) {
        die("Insert failed: " . $conn->error . "\n");
    }
    echo "Admin user created: {$username}\n";
}

echo "Username: {$username}\nPassword: {$password}\n";
echo "Login at: http://localhost:8000/admin/\n";
$conn->close();
