<?php
class ControllerStartupEnhancements extends Controller {
	public function index() {
		// Set better error reporting for development
		if (defined('DISPLAY_ERRORS') && DISPLAY_ERRORS) {
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);
		}
		
		// Increase session lifetime for admin
		ini_set('session.gc_maxlifetime', 7200); // 2 hours
		
		// Set better upload limits if needed
		$upload_max = ini_get('upload_max_filesize');
		$post_max = ini_get('post_max_size');
		
		// Log configuration for debugging
		if ($this->config->get('config_error_log')) {
			$log_message = sprintf(
				"[%s] Admin System Check - Upload Max: %s | Post Max: %s | Memory Limit: %s\n",
				date('Y-m-d H:i:s'),
				$upload_max,
				$post_max,
				ini_get('memory_limit')
			);
			
			@file_put_contents(DIR_LOGS . 'admin.log', $log_message, FILE_APPEND);
		}
		
		// Clean old cache files periodically (1% chance per request)
		if (rand(1, 100) === 1) {
			$this->cleanOldCache();
		}
	}
	
	/**
	 * Clean cache files older than 7 days
	 */
	private function cleanOldCache() {
		$cache_path = DIR_CACHE;
		$max_age = 604800; // 7 days
		
		$files = glob($cache_path . '*');
		if (!$files) {
			return;
		}
		
		$now = time();
		$count = 0;
		
		foreach ($files as $file) {
			if (is_file($file) && basename($file) != 'index.html') {
				if ($now - filemtime($file) >= $max_age) {
					@unlink($file);
					$count++;
				}
			}
		}
		
		if ($count > 0 && $this->config->get('config_error_log')) {
			$log_message = sprintf(
				"[%s] Cleaned %d old cache files\n",
				date('Y-m-d H:i:s'),
				$count
			);
			
			@file_put_contents(DIR_LOGS . 'admin.log', $log_message, FILE_APPEND);
		}
	}
}
