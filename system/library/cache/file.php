<?php
namespace Cache;
class File {
	private $expire;

	public function __construct($expire = 3600) {
		$this->expire = $expire;

		// Run cleanup on ~1% of requests to reduce I/O under high traffic (expired files are ignored on get)
		if (function_exists('random_int') && random_int(1, 100) !== 1) {
			return;
		}
		$files = glob(DIR_CACHE . 'cache.*');
		if ($files) {
			$now = time();
			foreach ($files as $file) {
				$time = substr(strrchr($file, '.'), 1);
				if (is_numeric($time) && (int)$time < $now && file_exists($file)) {
					@unlink($file);
				}
			}
		}
	}

	public function get($key) {
		$files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.*');

		if ($files) {
			$handle = fopen($files[0], 'r');

			flock($handle, LOCK_SH);

			$data = fread($handle, filesize($files[0]));

			flock($handle, LOCK_UN);

			fclose($handle);

			return json_decode($data, true);
		}

		return false;
	}

	public function set($key, $value) {
		$this->delete($key);

		$file = DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.' . (time() + $this->expire);

		$handle = fopen($file, 'w');

		flock($handle, LOCK_EX);

		fwrite($handle, json_encode($value));

		fflush($handle);

		flock($handle, LOCK_UN);

		fclose($handle);
	}

	public function delete($key) {
		$files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.*');

		if ($files) {
			foreach ($files as $file) {
				if (file_exists($file)) {
					unlink($file);
				}
			}
		}
	}
}