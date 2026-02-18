<?php
/**
 * One-time debug: show PHP errors that cause 500.
 * DELETE from server after use: rm tkurydyfjkv.diversiply.co/debug-500.php
 */
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
if (is_file('config.php')) {
    require_once('config.php');
}
if (!defined('DIR_APPLICATION')) {
    die('DIR_APPLICATION not defined');
}
require_once(DIR_SYSTEM . 'startup.php');
start('catalog');
