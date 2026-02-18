<?php
class ControllerStartupSass extends Controller {
	public function index() {
		$file = DIR_APPLICATION . 'view/stylesheet/bootstrap.css';

		if (!is_file($file) || !$this->config->get('developer_sass')) {
			try {
				$scss_inc = DIR_STORAGE . 'vendor/scss.inc.php';
				if (!is_file($scss_inc)) {
					$scss_inc = DIR_SYSTEM . 'storage/vendor/scss.inc.php';
				}
				if (is_file($scss_inc)) {
					include_once($scss_inc);
				}
				if (!class_exists('scssc')) {
					return;
				}
				$scss = new scssc();
				$scss->setImportPaths(DIR_APPLICATION . 'view/stylesheet/sass/');
				$output = $scss->compile('@import "_bootstrap.scss"');
				$handle = fopen($file, 'w');
				if ($handle !== false && is_resource($handle)) {
					flock($handle, LOCK_EX);
					fwrite($handle, $output);
					fflush($handle);
					flock($handle, LOCK_UN);
					fclose($handle);
				}
			} catch (Throwable $e) {
				// Skip SCSS compile on error so the page still loads
			}
		}
	}
}
