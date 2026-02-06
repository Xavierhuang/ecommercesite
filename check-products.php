<?php
// Check theme and product settings
$conn = new mysqli('127.0.0.1', 'root', 'root', 'dev_oc_4566');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get active theme
echo "=== ACTIVE THEME ===\n";
$result = $conn->query("SELECT * FROM oc_setting WHERE `key` LIKE '%theme%' AND store_id = 0 ORDER BY `key`");
while ($row = $result->fetch_assoc()) {
    echo $row['key'] . " = " . $row['value'] . "\n";
}

// Get product count by status
echo "\n=== PRODUCT COUNT BY STATUS ===\n";
$result = $conn->query("SELECT status, COUNT(*) as count FROM oc_product GROUP BY status");
while ($row = $result->fetch_assoc()) {
    $status = $row['status'] == 1 ? 'Enabled' : 'Disabled';
    echo "$status: " . $row['count'] . " products\n";
}

// Get some sample products
echo "\n=== SAMPLE PRODUCTS (First 10) ===\n";
$result = $conn->query("SELECT product_id, model, status FROM oc_product LIMIT 10");
while ($row = $result->fetch_assoc()) {
    $status = $row['status'] == 1 ? 'Enabled' : 'Disabled';
    echo "ID: " . $row['product_id'] . " | Model: " . $row['model'] . " | Status: $status\n";
}

// Get product names
echo "\n=== PRODUCT NAMES (First 10) ===\n";
$result = $conn->query("SELECT p.product_id, pd.name, p.status 
                        FROM oc_product p 
                        LEFT JOIN oc_product_description pd ON p.product_id = pd.product_id 
                        WHERE pd.language_id = 1 
                        LIMIT 10");
while ($row = $result->fetch_assoc()) {
    $status = $row['status'] == 1 ? 'Enabled' : 'Disabled';
    echo "ID: " . $row['product_id'] . " | Name: " . $row['name'] . " | Status: $status\n";
}

$conn->close();
