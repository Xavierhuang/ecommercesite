<?php
// Create a new admin user for OpenCart
// Usage: php create-admin.php

// Use same DB as admin (load from admin/config.php)
$admin_config = __DIR__ . '/admin/config.php';
if (is_file($admin_config)) {
    require_once $admin_config;
    $db_host = DB_HOSTNAME;
    $db_user = DB_USERNAME;
    $db_pass = DB_PASSWORD;
    $db_name = DB_DATABASE;
} else {
    $db_host = '127.0.0.1';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'dev_oc_4566';
}

// New admin details
$username = 'newadmin';
$password = 'admin123';
$email = 'newadmin@local.test';
$firstname = 'New';
$lastname = 'Admin';

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Generate salt and password hash (OpenCart uses SHA1 with salt)
$salt = substr(bin2hex(random_bytes(5)), 0, 9);
$password_hash = sha1($salt . sha1($salt . sha1($password)));

$tbl = (defined('DB_PREFIX') ? DB_PREFIX : 'oc_') . 'user';
$sql = "INSERT INTO `{$tbl}` (user_group_id, username, password, salt, firstname, lastname, email, image, code, ip, status, date_added) 
        VALUES (1, ?, ?, ?, ?, ?, ?, '', '', '', 1, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $username, $password_hash, $salt, $firstname, $lastname, $email);

if ($stmt->execute()) {
    echo "\n✓ Admin user created successfully!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Username: $username\n";
    echo "Password: $password\n";
    echo "Email: $email\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "\nYou can now login at: http://diversiply.test/admin/\n\n";
} else {
    if ($conn->errno == 1062) {
        echo "\n✗ Error: Username '$username' already exists. Choose a different username.\n\n";
    } else {
        echo "\n✗ Error: " . $stmt->error . "\n\n";
    }
}

$stmt->close();
$conn->close();
