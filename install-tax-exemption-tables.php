<?php
/**
 * One-time: create tax exemption tables on the database (uses config.php credentials).
 * Run once in browser: https://tkurydyfjkv.diversiply.co/install-tax-exemption-tables.php
 * Then delete from server for security: rm install-tax-exemption-tables.php
 */
header('Content-Type: text/plain; charset=utf-8');
if (!is_file('config.php')) {
    die('config.php not found. Run this script from the site root.');
}
require_once('config.php');
if (!defined('DB_PREFIX')) {
    die('DB_PREFIX not defined.');
}
$p = DB_PREFIX;
$driver = defined('DB_DRIVER') ? DB_DRIVER : 'mysqli';
if ($driver !== 'mysqli') {
    die('Only mysqli is supported.');
}
$mysqli = @new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, (int)DB_PORT);
if ($mysqli->connect_error) {
    die('DB connect failed: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8');

$tables = [
    "CREATE TABLE IF NOT EXISTS `{$p}customer_tax_exemption` (
      `exemption_id` int(11) NOT NULL AUTO_INCREMENT,
      `customer_id` int(11) NOT NULL,
      `exemption_type` enum('resale','nonprofit','government','state_exempt','other') NOT NULL,
      `certificate_number` varchar(100) NOT NULL,
      `issuing_state` varchar(50) DEFAULT NULL,
      `certificate_file` varchar(255) DEFAULT NULL,
      `business_name` varchar(255) DEFAULT NULL,
      `expiry_date` date DEFAULT NULL,
      `status` enum('pending','approved','rejected','expired') NOT NULL DEFAULT 'pending',
      `approved_by` int(11) DEFAULT NULL,
      `approved_date` datetime DEFAULT NULL,
      `rejected_by` int(11) DEFAULT NULL,
      `rejected_date` datetime DEFAULT NULL,
      `rejection_reason` text,
      `date_added` datetime NOT NULL,
      PRIMARY KEY (`exemption_id`),
      KEY `idx_customer` (`customer_id`),
      KEY `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
    "CREATE TABLE IF NOT EXISTS `{$p}customer_tax_exemption_log` (
      `log_id` int(11) NOT NULL AUTO_INCREMENT,
      `exemption_id` int(11) NOT NULL,
      `action` varchar(50) NOT NULL,
      `notes` text,
      `date_added` datetime NOT NULL,
      PRIMARY KEY (`log_id`),
      KEY `idx_exemption` (`exemption_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
];

foreach ($tables as $sql) {
    if (!$mysqli->query($sql)) {
        die('Error: ' . $mysqli->error);
    }
}
$mysqli->close();
echo "Tables created: {$p}customer_tax_exemption, {$p}customer_tax_exemption_log\n";
echo "Done. Delete this file from the server (install-tax-exemption-tables.php).\n";
