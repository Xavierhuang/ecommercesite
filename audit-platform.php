<?php
// Platform Audit Script
// Collects comprehensive system information

$report = [];

// 1. PHP VERSION & CONFIGURATION
$report['php']['version'] = phpversion();
$report['php']['memory_limit'] = ini_get('memory_limit');
$report['php']['max_execution_time'] = ini_get('max_execution_time');
$report['php']['upload_max_filesize'] = ini_get('upload_max_filesize');
$report['php']['post_max_size'] = ini_get('post_max_size');
$report['php']['display_errors'] = ini_get('display_errors');
$report['php']['error_reporting'] = ini_get('error_reporting');

// 2. PHP EXTENSIONS
$report['php']['extensions'] = get_loaded_extensions();

// 3. DATABASE INFO
// Match config.php: default root with no password; override via env if needed
$db_host = getenv('DB_HOST') ?: '127.0.0.1';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$db_name = getenv('DB_NAME') ?: 'dev_oc_4566';
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$report['database']['version'] = $conn->server_info;
$report['database']['name'] = $db_name;

// Get table count
$result = $conn->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = '" . $conn->real_escape_string($db_name) . "'");
$row = $result->fetch_assoc();
$report['database']['table_count'] = $row['count'];

// 4. OPENCART VERSION & SETTINGS
$result = $conn->query("SELECT * FROM oc_setting WHERE store_id = 0 ORDER BY `key`");
$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[$row['key']] = $row['value'];
}
$report['opencart']['version'] = $settings['config_opencart_version'] ?? 'Unknown';
$report['opencart']['name'] = $settings['config_name'] ?? 'Unknown';
$report['opencart']['email'] = $settings['config_email'] ?? 'Unknown';
$report['opencart']['theme'] = $settings['theme_default_directory'] ?? 'Unknown';

// 5. INSTALLED EXTENSIONS
$result = $conn->query("SELECT * FROM oc_extension ORDER BY type, code");
$extensions = [];
while ($row = $result->fetch_assoc()) {
    $extensions[] = [
        'type' => $row['type'],
        'code' => $row['code']
    ];
}
$report['extensions'] = $extensions;

// 6. MODULES
$result = $conn->query("SELECT module_id, name, code FROM oc_module ORDER BY name");
$modules = [];
while ($row = $result->fetch_assoc()) {
    $modules[] = [
        'id' => $row['module_id'],
        'name' => $row['name'],
        'code' => $row['code']
    ];
}
$report['modules'] = $modules;

// 7. USERS
$result = $conn->query("SELECT COUNT(*) as count FROM oc_user WHERE status = 1");
$row = $result->fetch_assoc();
$report['users']['admin_count'] = $row['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM oc_customer WHERE status = 1");
$row = $result->fetch_assoc();
$report['users']['customer_count'] = $row['count'];

// 8. PRODUCTS
$result = $conn->query("SELECT status, COUNT(*) as count FROM oc_product GROUP BY status");
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[$row['status'] == 1 ? 'enabled' : 'disabled'] = $row['count'];
}
$report['products'] = $products;

// 9. ORDERS
$result = $conn->query("SELECT COUNT(*) as count FROM oc_order");
$row = $result->fetch_assoc();
$report['orders']['total'] = $row['count'];

$result = $conn->query("SELECT order_status_id, COUNT(*) as count FROM oc_order GROUP BY order_status_id");
$order_status = [];
while ($row = $result->fetch_assoc()) {
    $order_status[$row['order_status_id']] = $row['count'];
}
$report['orders']['by_status'] = $order_status;

// 10. EVENTS/TRIGGERS
$result = $conn->query("SELECT * FROM oc_event WHERE status = 1 ORDER BY `trigger`");
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = [
        'trigger' => $row['trigger'],
        'action' => $row['action'],
        'sort_order' => $row['sort_order']
    ];
}
$report['events'] = $events;

$conn->close();

// OUTPUT REPORT
echo "═══════════════════════════════════════════════════════════\n";
echo "           DIVERSIPLY PLATFORM AUDIT REPORT\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "1. PHP CONFIGURATION\n";
echo "   Version: " . $report['php']['version'] . "\n";
echo "   Memory Limit: " . $report['php']['memory_limit'] . "\n";
echo "   Max Execution Time: " . $report['php']['max_execution_time'] . "s\n";
echo "   Upload Max Filesize: " . $report['php']['upload_max_filesize'] . "\n";
echo "   Post Max Size: " . $report['php']['post_max_size'] . "\n\n";

echo "2. PHP EXTENSIONS (" . count($report['php']['extensions']) . " loaded)\n";
$important_extensions = ['mysqli', 'curl', 'gd', 'zip', 'mbstring', 'xml', 'json', 'openssl'];
foreach ($important_extensions as $ext) {
    $status = in_array($ext, $report['php']['extensions']) ? '✓' : '✗';
    echo "   $status $ext\n";
}
echo "\n";

echo "3. DATABASE\n";
echo "   MySQL Version: " . $report['database']['version'] . "\n";
echo "   Database: " . $report['database']['name'] . "\n";
echo "   Tables: " . $report['database']['table_count'] . "\n\n";

echo "4. OPENCART\n";
echo "   Version: " . $report['opencart']['version'] . "\n";
echo "   Store Name: " . $report['opencart']['name'] . "\n";
echo "   Email: " . $report['opencart']['email'] . "\n";
echo "   Theme: " . $report['opencart']['theme'] . "\n\n";

echo "5. EXTENSIONS (" . count($report['extensions']) . " installed)\n";
$extension_types = [];
foreach ($report['extensions'] as $ext) {
    $extension_types[$ext['type']] = ($extension_types[$ext['type']] ?? 0) + 1;
}
foreach ($extension_types as $type => $count) {
    echo "   $type: $count\n";
}
echo "\n";

echo "6. MODULES (" . count($report['modules']) . " configured)\n";
echo "   Notable modules:\n";
foreach (array_slice($report['modules'], 0, 10) as $module) {
    echo "   - " . $module['name'] . " (" . $module['code'] . ")\n";
}
if (count($report['modules']) > 10) {
    echo "   ... and " . (count($report['modules']) - 10) . " more\n";
}
echo "\n";

echo "7. USERS\n";
echo "   Admin Users: " . $report['users']['admin_count'] . "\n";
echo "   Customers: " . $report['users']['customer_count'] . "\n\n";

echo "8. PRODUCTS\n";
echo "   Enabled: " . ($report['products']['enabled'] ?? 0) . "\n";
echo "   Disabled: " . ($report['products']['disabled'] ?? 0) . "\n\n";

echo "9. ORDERS\n";
echo "   Total: " . $report['orders']['total'] . "\n";
if (!empty($report['orders']['by_status'])) {
    echo "   By Status:\n";
    foreach ($report['orders']['by_status'] as $status_id => $count) {
        echo "     Status $status_id: $count\n";
    }
}
echo "\n";

echo "10. ACTIVE EVENTS/TRIGGERS (" . count($report['events']) . ")\n";
foreach ($report['events'] as $event) {
    echo "   - " . $event['trigger'] . " → " . $event['action'] . "\n";
}
echo "\n";

echo "═══════════════════════════════════════════════════════════\n";
echo "                    AUDIT COMPLETE\n";
echo "═══════════════════════════════════════════════════════════\n";

// Save to file
file_put_contents('devdocs/work/audit/audit-data.json', json_encode($report, JSON_PRETTY_PRINT));
echo "\nDetailed report saved to: devdocs/work/audit/audit-data.json\n";
