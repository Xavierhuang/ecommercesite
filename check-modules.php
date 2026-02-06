<?php
// Check layout modules for home page
$conn = new mysqli('127.0.0.1', 'root', 'root', 'dev_oc_4566');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get home page layout ID
echo "=== HOME PAGE LAYOUT ===\n";
$result = $conn->query("SELECT * FROM oc_layout WHERE name LIKE '%home%' OR name LIKE '%Home%'");
$layout_id = null;
while ($row = $result->fetch_assoc()) {
    echo "Layout ID: " . $row['layout_id'] . " | Name: " . $row['name'] . "\n";
    $layout_id = $row['layout_id'];
}

if ($layout_id) {
    echo "\n=== MODULES ON HOME PAGE ===\n";
    $result = $conn->query("SELECT * FROM oc_layout_route WHERE layout_id = $layout_id");
    while ($row = $result->fetch_assoc()) {
        echo "Route: " . $row['route'] . "\n";
    }
    
    echo "\n=== LAYOUT MODULES ===\n";
    $result = $conn->query("SELECT * FROM oc_layout_module WHERE layout_id = $layout_id ORDER BY position, sort_order");
    while ($row = $result->fetch_assoc()) {
        echo "Position: " . $row['position'] . " | Code: " . $row['code'] . " | Sort: " . $row['sort_order'] . "\n";
    }
    
    // Get module settings
    echo "\n=== MODULE SETTINGS ===\n";
    $result = $conn->query("SELECT * FROM oc_module WHERE module_id IN (53, 215, 218, 446, 501, 191, 40, 532, 533) OR code LIKE '%product%'");
    while ($row = $result->fetch_assoc()) {
        echo "\nModule ID: " . $row['module_id'] . " | Name: " . $row['name'] . " | Code: " . $row['code'] . "\n";
        $setting = json_decode($row['setting'], true);
        if (isset($setting['product']) && is_array($setting['product'])) {
            echo "Products: " . implode(', ', $setting['product']) . "\n";
        }
        if (isset($setting['limit'])) {
            echo "Limit: " . $setting['limit'] . "\n";
        }
        if (isset($setting['status'])) {
            echo "Status: " . ($setting['status'] ? 'Enabled' : 'Disabled') . "\n";
        }
    }
}

$conn->close();
