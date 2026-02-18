<?php
// Show errors on blank page (remove in production)
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Version
define('VERSION', '3.0.3.2');

// Configuration (use __DIR__ so it works when front controller is run from project root, e.g. router.php)
if (is_file(__DIR__ . '/config.php')) {
	require_once(__DIR__ . '/config.php');
}

// Install
if (!defined('DIR_APPLICATION')) {
	header('Location: ../install/index.php');
	exit;
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

start('admin');