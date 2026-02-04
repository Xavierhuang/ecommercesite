<?php
namespace Template;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

final class Twig {
	private $twig;
	private $data = array();

	public function __construct() {
		// Load Composer autoloader from vendor folder
		require_once(DIR_APPLICATION . '../vendor/autoload.php');
	}

	public function set($key, $value) {
		$this->data[$key] = $value;
	}

	public function render($template, $cache = false) {
		// Ensure DIR_TEMPLATE is valid and fallback if needed
		$template_path = defined('DIR_TEMPLATE') && is_dir(DIR_TEMPLATE)
			? realpath(DIR_TEMPLATE)
			: realpath(DIR_APPLICATION . 'view/theme/');

		// Fallback if realpath returns false (e.g., symbolic link or unreadable dir)
		if (!$template_path) {
			$template_path = DIR_TEMPLATE ?: (DIR_APPLICATION . 'view/theme/');
		}

		// Setup Twig template loader
		$loader = new FilesystemLoader($template_path);

		// Twig config
		$config = array(
			'autoescape' => false
		);

		if ($cache) {
			$config['cache'] = DIR_CACHE;
		}

		// Init Twig
		$this->twig = new Environment($loader, $config);

		try {
			// Render and return template output
			return $this->twig->render($template . '.twig', $this->data);
		} catch (\Twig\Error\LoaderError |
		         \Twig\Error\RuntimeError |
		         \Twig\Error\SyntaxError $e) {
			trigger_error('Error: Could not load template ' . $template . '.twig - ' . $e->getMessage());
			exit();
		}
	}
}
