<?php
// Temporary script to reset admin password
// Delete this file after use!

$mysqli = new mysqli('127.0.0.1', 'root', 'root', 'dev_oc_4566', 3306);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Generate password hash for "admin123"
$new_password = 'admin123';
$password_hash = password_hash($new_password, PASSWORD_DEFAULT);

// Update admin user (user_id = 1)
$stmt = $mysqli->prepare("UPDATE oc_user SET password = ? WHERE user_id = 1");
$stmt->bind_param("s", $password_hash);

if ($stmt->execute()) {
    echo "Success! Admin password has been reset.\n";
    echo "Username: admin\n";
    echo "Password: " . $new_password . "\n";
    echo "\nYou can now login at: http://diversiply.test/admin/\n";
    echo "\n*** IMPORTANT: Delete this file (reset-admin-password.php) after use! ***\n";
} else {
    echo "Error updating password: " . $stmt->error;
}

$stmt->close();
$mysqli->close();
