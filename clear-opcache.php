<?php
/**
 * One-time script: clear PHP OPcache so updated PHP files are used.
 * Call once after deploy: https://tkurydyfjkv.diversiply.co/clear-opcache.php
 * Delete this file from the server after use (security).
 */
header('Content-Type: text/plain');
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared. Updated PHP files will be used on next request.\n";
} else {
    echo "OPcache not enabled or not available.\n";
}
