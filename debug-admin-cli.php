<?php
/**
 * Run admin bootstrap from CLI to see output length or catch errors.
 * Usage: php debug-admin-cli.php
 */
chdir(__DIR__ . '/admin');
$_GET['route'] = 'common/dashboard';
ini_set('display_errors', '1');
error_reporting(E_ALL);
ob_start();
try {
    require 'index.php';
    $out = ob_get_clean();
    echo 'Length: ' . strlen($out) . PHP_EOL;
    if (strlen($out) < 1500) {
        echo $out;
    } else {
        echo substr($out, 0, 500) . "\n... (truncated)\n";
    }
} catch (Throwable $e) {
    ob_end_clean();
    echo get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
}
