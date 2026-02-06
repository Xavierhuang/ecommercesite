-- Email Queue Table for better notification management
CREATE TABLE IF NOT EXISTS `oc_email_queue` (
  `email_queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `to_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `priority` tinyint(1) NOT NULL DEFAULT '2',
  `status` enum('pending','processing','sent','failed') NOT NULL DEFAULT 'pending',
  `attempts` int(11) NOT NULL DEFAULT '0',
  `send_after` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `last_attempt` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`email_queue_id`),
  KEY `idx_status_priority` (`status`,`priority`,`send_after`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Product visibility tracking
CREATE TABLE IF NOT EXISTS `oc_product_visibility_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `old_status` tinyint(1) DEFAULT NULL,
  `new_status` tinyint(1) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(40) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- SEO URL audit log
CREATE TABLE IF NOT EXISTS `oc_seo_url_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `seo_url_id` int(11) DEFAULT NULL,
  `query` varchar(255) NOT NULL,
  `keyword` varchar(255) NOT NULL,
  `action` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `idx_query` (`query`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Cache statistics
CREATE TABLE IF NOT EXISTS `oc_cache_stats` (
  `stat_id` int(11) NOT NULL AUTO_INCREMENT,
  `cache_key` varchar(100) NOT NULL,
  `action` enum('hit','miss','delete') NOT NULL,
  `count` int(11) NOT NULL DEFAULT '1',
  `last_access` datetime NOT NULL,
  PRIMARY KEY (`stat_id`),
  UNIQUE KEY `idx_key_action` (`cache_key`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Error log table for better tracking
CREATE TABLE IF NOT EXISTS `oc_error_log` (
  `error_id` int(11) NOT NULL AUTO_INCREMENT,
  `error_type` varchar(50) NOT NULL,
  `error_message` text NOT NULL,
  `file` varchar(255) DEFAULT NULL,
  `line` int(11) DEFAULT NULL,
  `url` text,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(40) DEFAULT NULL,
  `user_agent` text,
  `context` text,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`error_id`),
  KEY `idx_type_date` (`error_type`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Session activity tracking
CREATE TABLE IF NOT EXISTS `oc_session_activity` (
  `activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(32) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `ip_address` varchar(40) DEFAULT NULL,
  `user_agent` text,
  `last_activity` datetime NOT NULL,
  `page_views` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`activity_id`),
  UNIQUE KEY `idx_session` (`session_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tax exemption certificates
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

-- Tax exemption activity log
CREATE TABLE IF NOT EXISTS `oc_customer_tax_exemption_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `exemption_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `notes` text,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `idx_exemption` (`exemption_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
