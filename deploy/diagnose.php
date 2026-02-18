<?php
/**
 * Minimal diagnostic - no config loaded. Delete after use.
 */
header('Content-Type: text/plain');
echo "OK: PHP works\n";
echo "CWD: " . getcwd() . "\n";
echo "Dir: " . __DIR__ . "\n";
echo "File exists: " . (file_exists(__DIR__ . '/../config.php') ? 'yes' : 'no') . "\n";
