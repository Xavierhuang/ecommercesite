<?php
/**
 * Session Helper Utilities
 * Provides better session management and security
 */
class SessionHelper {
	private $session;
	private $db;
	
	public function __construct($session, $db = null) {
		$this->session = $session;
		$this->db = $db;
	}
	
	/**
	 * Clean up expired sessions
	 */
	public function cleanExpiredSessions($max_age = 86400) {
		if (!$this->db) {
			return false;
		}
		
		// Clean session files older than max_age
		$session_path = session_save_path();
		if (empty($session_path)) {
			$session_path = DIR_SESSION;
		}
		
		$files = glob($session_path . '/sess_*');
		$now = time();
		$count = 0;
		
		foreach ($files as $file) {
			if (is_file($file)) {
				if ($now - filemtime($file) >= $max_age) {
					@unlink($file);
					$count++;
				}
			}
		}
		
		return $count;
	}
	
	/**
	 * Regenerate session ID for security
	 */
	public function regenerateSession() {
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_regenerate_id(true);
			return session_id();
		}
		return false;
	}
	
	/**
	 * Set session with expiration time
	 */
	public function setWithExpiry($key, $value, $expiry_seconds = 3600) {
		$this->session->data[$key] = array(
			'value' => $value,
			'expires' => time() + $expiry_seconds
		);
	}
	
	/**
	 * Get session value with expiration check
	 */
	public function getWithExpiry($key) {
		if (!isset($this->session->data[$key])) {
			return null;
		}
		
		$data = $this->session->data[$key];
		
		if (is_array($data) && isset($data['expires'])) {
			if (time() > $data['expires']) {
				unset($this->session->data[$key]);
				return null;
			}
			return $data['value'];
		}
		
		return $data;
	}
	
	/**
	 * Flash message - set message that expires after being read
	 */
	public function flash($key, $value = null) {
		if ($value === null) {
			// Get and delete
			if (isset($this->session->data['flash_' . $key])) {
				$value = $this->session->data['flash_' . $key];
				unset($this->session->data['flash_' . $key]);
				return $value;
			}
			return null;
		} else {
			// Set
			$this->session->data['flash_' . $key] = $value;
		}
	}
	
	/**
	 * Get session size
	 */
	public function getSize() {
		return strlen(serialize($this->session->data));
	}
	
	/**
	 * Check if session is bloated
	 */
	public function isBloated($max_size = 4096) {
		return $this->getSize() > $max_size;
	}
}
