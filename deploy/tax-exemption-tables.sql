-- Create tax exemption tables if missing (required for TaxExemptionHelper).
-- Run on dev DB: mysql -u USER -p tkurydyfjkv_oc < deploy/tax-exemption-tables.sql
-- Or in phpMyAdmin: paste and run this SQL.

CREATE TABLE IF NOT EXISTS `oc_customer_tax_exemption` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oc_customer_tax_exemption_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `exemption_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `notes` text,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `idx_exemption` (`exemption_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
