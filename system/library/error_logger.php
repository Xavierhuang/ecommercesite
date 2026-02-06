<?php
/**
 * Enhanced Error Logger
 * Provides detailed error logging with context
 */
class ErrorLogger {
	private $log_file;
	private $enabled = true;
	
	public function __construct($log_file = null) {
		if ($log_file === null) {
			$log_file = DIR_LOGS . 'error.log';
		}
		$this->log_file = $log_file;
	}
	
	/**
	 * Log an error with context
	 */
	public function logError($message, $context = array(), $severity = 'ERROR') {
		if (!$this->enabled) {
			return;
		}
		
		$timestamp = date('Y-m-d H:i:s');
		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
		$url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'Unknown';
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'Unknown';
		
		$log_message = sprintf(
			"[%s] %s: %s\n",
			$timestamp,
			$severity,
			$message
		);
		
		$log_message .= sprintf(
			"  IP: %s | Method: %s | URL: %s\n",
			$ip,
			$method,
			$url
		);
		
		if (!empty($context)) {
			$log_message .= "  Context: " . json_encode($context) . "\n";
		}
		
		if (isset($context['trace'])) {
			$log_message .= "  Stack Trace:\n";
			foreach ($context['trace'] as $index => $trace) {
				if (isset($trace['file']) && isset($trace['line'])) {
					$log_message .= sprintf("    #%d %s:%d\n", $index, $trace['file'], $trace['line']);
				}
			}
		}
		
		$log_message .= str_repeat('-', 80) . "\n";
		
		@file_put_contents($this->log_file, $log_message, FILE_APPEND);
	}
	
	/**
	 * Log exception
	 */
	public function logException($exception) {
		$context = array(
			'message' => $exception->getMessage(),
			'code' => $exception->getCode(),
			'file' => $exception->getFile(),
			'line' => $exception->getLine(),
			'trace' => $exception->getTrace()
		);
		
		$this->logError('Exception: ' . $exception->getMessage(), $context, 'EXCEPTION');
	}
	
	/**
	 * Log upload error
	 */
	public function logUploadError($file_info) {
		$error_messages = array(
			UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize in php.ini',
			UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE in HTML form',
			UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
			UPLOAD_ERR_NO_FILE => 'No file was uploaded',
			UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
			UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
			UPLOAD_ERR_EXTENSION => 'PHP extension stopped the file upload'
		);
		
		$error_code = isset($file_info['error']) ? $file_info['error'] : 'Unknown';
		$error_message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Unknown upload error';
		
		$context = array(
			'filename' => isset($file_info['name']) ? $file_info['name'] : 'Unknown',
			'size' => isset($file_info['size']) ? $file_info['size'] : 0,
			'type' => isset($file_info['type']) ? $file_info['type'] : 'Unknown',
			'error_code' => $error_code,
			'error_message' => $error_message
		);
		
		$this->logError('Upload Error: ' . $error_message, $context, 'UPLOAD_ERROR');
	}
}
