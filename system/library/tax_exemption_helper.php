<?php
/**
 * Tax Exemption Helper
 * Handles tax exemption certificates and validation
 */
class TaxExemptionHelper {
	private $db;
	private $config;
	
	public function __construct($db, $config) {
		$this->db = $db;
		$this->config = $config;
	}
	
	/**
	 * Check if customer has valid tax exemption
	 * Returns false if table does not exist (e.g. install-enhancements.sql not run).
	 */
	public function hasValidExemption($customer_id) {
		try {
			$query = $this->db->query("
				SELECT * FROM " . DB_PREFIX . "customer_tax_exemption 
				WHERE customer_id = '" . (int)$customer_id . "' 
				AND status = 'approved'
				AND (expiry_date = '0000-00-00' OR expiry_date >= CURDATE())
			");
			return $query->num_rows > 0;
		} catch (\Throwable $e) {
			return false;
		}
	}
	
	/**
	 * Get customer tax exemption details
	 * Returns null if table does not exist.
	 */
	public function getExemptionDetails($customer_id) {
		try {
			$query = $this->db->query("
				SELECT * FROM " . DB_PREFIX . "customer_tax_exemption 
				WHERE customer_id = '" . (int)$customer_id . "' 
				ORDER BY date_added DESC 
				LIMIT 1
			");
			if ($query->num_rows) {
				return $query->row;
			}
		} catch (\Throwable $e) {
			// Table may not exist
		}
		return null;
	}
	
	/**
	 * Submit tax exemption certificate
	 */
	public function submitExemption($customer_id, $data) {
		$this->db->query("
			INSERT INTO " . DB_PREFIX . "customer_tax_exemption 
			SET customer_id = '" . (int)$customer_id . "',
				exemption_type = '" . $this->db->escape($data['exemption_type']) . "',
				certificate_number = '" . $this->db->escape($data['certificate_number']) . "',
				issuing_state = '" . $this->db->escape($data['issuing_state']) . "',
				certificate_file = '" . $this->db->escape($data['certificate_file'] ?? '') . "',
				business_name = '" . $this->db->escape($data['business_name'] ?? '') . "',
				expiry_date = '" . $this->db->escape($data['expiry_date'] ?? '0000-00-00') . "',
				status = 'pending',
				date_added = NOW()
		");
		
		$exemption_id = $this->db->getLastId();
		
		// Log the submission
		$this->logExemptionActivity($exemption_id, 'submitted', 'Tax exemption certificate submitted for review');
		
		return $exemption_id;
	}
	
	/**
	 * Approve tax exemption
	 */
	public function approveExemption($exemption_id, $admin_id) {
		$this->db->query("
			UPDATE " . DB_PREFIX . "customer_tax_exemption 
			SET status = 'approved',
				approved_by = '" . (int)$admin_id . "',
				approved_date = NOW()
			WHERE exemption_id = '" . (int)$exemption_id . "'
		");
		
		$this->logExemptionActivity($exemption_id, 'approved', 'Tax exemption approved by admin');
		
		// Notify customer
		$this->sendExemptionNotification($exemption_id, 'approved');
	}
	
	/**
	 * Reject tax exemption
	 */
	public function rejectExemption($exemption_id, $admin_id, $reason = '') {
		$this->db->query("
			UPDATE " . DB_PREFIX . "customer_tax_exemption 
			SET status = 'rejected',
				rejected_by = '" . (int)$admin_id . "',
				rejection_reason = '" . $this->db->escape($reason) . "',
				rejected_date = NOW()
			WHERE exemption_id = '" . (int)$exemption_id . "'
		");
		
		$this->logExemptionActivity($exemption_id, 'rejected', 'Tax exemption rejected: ' . $reason);
		
		// Notify customer
		$this->sendExemptionNotification($exemption_id, 'rejected');
	}
	
	/**
	 * Calculate tax for order with exemption
	 */
	public function calculateTax($customer_id, $subtotal, $shipping_address) {
		// Check if customer has exemption
		if ($this->hasValidExemption($customer_id)) {
			$exemption = $this->getExemptionDetails($customer_id);
			
			// Check if exemption applies to this state
			if ($this->exemptionApplies($exemption, $shipping_address)) {
				return 0;
			}
		}
		
		// Calculate normal tax
		return $this->calculateNormalTax($subtotal, $shipping_address);
	}
	
	/**
	 * Check if exemption applies to address
	 */
	private function exemptionApplies($exemption, $address) {
		// Resale certificate - applies to all states
		if ($exemption['exemption_type'] == 'resale') {
			return true;
		}
		
		// State-specific exemption
		if ($exemption['exemption_type'] == 'state_exempt') {
			return $address['zone_id'] == $exemption['issuing_state'];
		}
		
		// Non-profit exemption
		if ($exemption['exemption_type'] == 'nonprofit') {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Calculate normal tax rate
	 */
	private function calculateNormalTax($subtotal, $address) {
		// Get tax rate for zone
		$query = $this->db->query("
			SELECT rate FROM " . DB_PREFIX . "tax_rate 
			WHERE geo_zone_id IN (
				SELECT geo_zone_id FROM " . DB_PREFIX . "zone_to_geo_zone 
				WHERE country_id = '" . (int)$address['country_id'] . "' 
				AND (zone_id = '0' OR zone_id = '" . (int)$address['zone_id'] . "')
			)
			ORDER BY priority DESC 
			LIMIT 1
		");
		
		if ($query->num_rows) {
			$rate = $query->row['rate'];
			return ($subtotal * $rate) / 100;
		}
		
		return 0;
	}
	
	/**
	 * Log exemption activity
	 */
	private function logExemptionActivity($exemption_id, $action, $notes = '') {
		$this->db->query("
			INSERT INTO " . DB_PREFIX . "customer_tax_exemption_log 
			SET exemption_id = '" . (int)$exemption_id . "',
				action = '" . $this->db->escape($action) . "',
				notes = '" . $this->db->escape($notes) . "',
				date_added = NOW()
		");
	}
	
	/**
	 * Send exemption notification
	 */
	private function sendExemptionNotification($exemption_id, $status) {
		// Get exemption and customer details
		$query = $this->db->query("
			SELECT e.*, c.firstname, c.lastname, c.email 
			FROM " . DB_PREFIX . "customer_tax_exemption e
			LEFT JOIN " . DB_PREFIX . "customer c ON (e.customer_id = c.customer_id)
			WHERE e.exemption_id = '" . (int)$exemption_id . "'
		");
		
		if (!$query->num_rows) {
			return;
		}
		
		$data = $query->row;
		
		// Send email notification
		$subject = 'Tax Exemption ' . ucfirst($status) . ' - ' . $this->config->get('config_name');
		
		$message = sprintf("Dear %s %s,\n\n", $data['firstname'], $data['lastname']);
		$message .= "Your tax exemption certificate has been " . $status . ".\n\n";
		
		if ($status == 'rejected' && !empty($data['rejection_reason'])) {
			$message .= "Reason: " . $data['rejection_reason'] . "\n\n";
		}
		
		if ($status == 'approved') {
			$message .= "Your tax exemption is now active and will be applied to eligible orders.\n\n";
		}
		
		$message .= "Thank you,\n";
		$message .= $this->config->get('config_name');
		
		// Queue email (using EmailNotificationManager if available)
		// mail($data['email'], $subject, $message);
	}
	
	/**
	 * Get pending exemption requests
	 */
	public function getPendingExemptions() {
		$query = $this->db->query("
			SELECT e.*, 
				   CONCAT(c.firstname, ' ', c.lastname) as customer_name,
				   c.email
			FROM " . DB_PREFIX . "customer_tax_exemption e
			LEFT JOIN " . DB_PREFIX . "customer c ON (e.customer_id = c.customer_id)
			WHERE e.status = 'pending'
			ORDER BY e.date_added DESC
		");
		
		return $query->rows;
	}
	
	/**
	 * Validate exemption certificate number format
	 */
	public function validateCertificateNumber($number, $type, $state) {
		// Basic validation - can be expanded per state requirements
		if (empty($number)) {
			return false;
		}
		
		// Remove spaces and special characters
		$number = preg_replace('/[^A-Z0-9]/', '', strtoupper($number));
		
		// Must be at least 5 characters
		if (strlen($number) < 5) {
			return false;
		}
		
		return true;
	}
}
