<?php
/**
 * Email Notification Manager
 * Centralized email notification system with queue support
 */
class EmailNotificationManager {
	private $config;
	private $db;
	private $log;
	
	const PRIORITY_HIGH = 1;
	const PRIORITY_NORMAL = 2;
	const PRIORITY_LOW = 3;
	
	public function __construct($config, $db, $log = null) {
		$this->config = $config;
		$this->db = $db;
		$this->log = $log;
	}
	
	/**
	 * Send email immediately
	 */
	public function send($to, $subject, $message, $from = null, $sender = null) {
		try {
			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->setTo($to);
			$mail->setFrom($from ?: $this->config->get('config_email'));
			$mail->setSender($sender ?: html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setText($message);
			$mail->send();
			
			$this->logEmail($to, $subject, 'sent');
			return true;
		} catch (Exception $e) {
			$this->logEmail($to, $subject, 'failed', $e->getMessage());
			return false;
		}
	}
	
	/**
	 * Queue email for later sending
	 */
	public function queue($to, $subject, $message, $priority = self::PRIORITY_NORMAL, $send_after = null) {
		$send_after = $send_after ?: date('Y-m-d H:i:s');
		
		$this->db->query("
			INSERT INTO " . DB_PREFIX . "email_queue 
			SET to_email = '" . $this->db->escape($to) . "',
				subject = '" . $this->db->escape($subject) . "',
				message = '" . $this->db->escape($message) . "',
				priority = '" . (int)$priority . "',
				status = 'pending',
				send_after = '" . $this->db->escape($send_after) . "',
				created_at = NOW()
		");
		
		return $this->db->getLastId();
	}
	
	/**
	 * Process email queue
	 */
	public function processQueue($limit = 10) {
		$query = $this->db->query("
			SELECT * FROM " . DB_PREFIX . "email_queue 
			WHERE status = 'pending' 
			AND send_after <= NOW()
			ORDER BY priority ASC, created_at ASC 
			LIMIT " . (int)$limit
		);
		
		$sent = 0;
		$failed = 0;
		
		foreach ($query->rows as $row) {
			// Mark as processing
			$this->db->query("
				UPDATE " . DB_PREFIX . "email_queue 
				SET status = 'processing', 
					attempts = attempts + 1,
					last_attempt = NOW()
				WHERE email_queue_id = '" . (int)$row['email_queue_id'] . "'
			");
			
			// Try to send
			$success = $this->send($row['to_email'], $row['subject'], $row['message']);
			
			if ($success) {
				$this->db->query("
					UPDATE " . DB_PREFIX . "email_queue 
					SET status = 'sent', 
						sent_at = NOW()
					WHERE email_queue_id = '" . (int)$row['email_queue_id'] . "'
				");
				$sent++;
			} else {
				// Retry logic - max 3 attempts
				if ($row['attempts'] >= 3) {
					$this->db->query("
						UPDATE " . DB_PREFIX . "email_queue 
						SET status = 'failed'
						WHERE email_queue_id = '" . (int)$row['email_queue_id'] . "'
					");
				} else {
					// Reset to pending for retry
					$this->db->query("
						UPDATE " . DB_PREFIX . "email_queue 
						SET status = 'pending',
							send_after = DATE_ADD(NOW(), INTERVAL 5 MINUTE)
						WHERE email_queue_id = '" . (int)$row['email_queue_id'] . "'
					");
				}
				$failed++;
			}
			
			// Small delay between emails
			usleep(100000); // 0.1 second
		}
		
		return array('sent' => $sent, 'failed' => $failed);
	}
	
	/**
	 * Send order confirmation email
	 */
	public function sendOrderConfirmation($order_id, $customer_email, $customer_name) {
		$subject = sprintf('Order Confirmation #%s - %s', $order_id, $this->config->get('config_name'));
		
		$message = sprintf("Dear %s,\n\n", $customer_name);
		$message .= sprintf("Thank you for your order #%s!\n\n", $order_id);
		$message .= "Your order has been received and is being processed.\n\n";
		$message .= "You can track your order status in your account.\n\n";
		$message .= sprintf("Thank you for shopping with %s!\n", $this->config->get('config_name'));
		
		return $this->queue($customer_email, $subject, $message, self::PRIORITY_HIGH);
	}
	
	/**
	 * Send seller notification email
	 */
	public function sendSellerNotification($seller_email, $subject, $message) {
		return $this->queue($seller_email, $subject, $message, self::PRIORITY_NORMAL);
	}
	
	/**
	 * Log email activity
	 */
	private function logEmail($to, $subject, $status, $error = '') {
		if (!$this->log) {
			return;
		}
		
		$message = sprintf(
			"[%s] Email %s - To: %s | Subject: %s",
			date('Y-m-d H:i:s'),
			$status,
			$to,
			$subject
		);
		
		if ($error) {
			$message .= " | Error: " . $error;
		}
		
		$this->log->write($message);
	}
	
	/**
	 * Clean old email queue records
	 */
	public function cleanOldRecords($days = 30) {
		$this->db->query("
			DELETE FROM " . DB_PREFIX . "email_queue 
			WHERE status IN ('sent', 'failed') 
			AND created_at < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
		");
		
		return $this->db->countAffected();
	}
}
