<?php
// Run on server: php test-smtp-server.php
// Delete after use!
// Tests Gmail SMTP and shows any errors.
// Usage: SMTP_PASS=your_app_password php test-smtp-server.php

$hostname = 'smtp.gmail.com';
$port = 587;
$username = 'diversiply@gmail.com';
$password = getenv('SMTP_PASS') ?: 'Mad3Th3Mov3s2023';

echo "Testing SMTP connection to {$hostname}:{$port}...\n";

$handle = @fsockopen($hostname, $port, $errno, $errstr, 5);
if (!$handle) {
    echo "FAIL: Could not connect - $errstr ($errno)\n";
    exit(1);
}
echo "Connected.\n";

$line = fgets($handle, 515);
echo "Server: " . trim($line) . "\n";

fputs($handle, "EHLO " . (getenv('SERVER_NAME') ?: 'localhost') . "\r\n");
$reply = '';
while ($line = fgets($handle, 515)) {
    $reply .= $line;
    if (substr($line, 3, 1) == ' ') break;
}
echo "EHLO: " . trim($reply) . "\n";

fputs($handle, "STARTTLS\r\n");
$reply = '';
while ($line = fgets($handle, 515)) {
    $reply .= $line;
    if (substr($line, 3, 1) == ' ') break;
}
echo "STARTTLS: " . trim($reply) . "\n";
if (substr($reply, 0, 3) != '220') {
    echo "FAIL: STARTTLS not accepted\n";
    fclose($handle);
    exit(1);
}

stream_socket_enable_crypto($handle, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
echo "TLS enabled.\n";

fputs($handle, "EHLO localhost\r\n");
$reply = '';
while ($line = fgets($handle, 515)) {
    $reply .= $line;
    if (substr($line, 3, 1) == ' ') break;
}

fputs($handle, "AUTH LOGIN\r\n");
$reply = '';
while ($line = fgets($handle, 515)) {
    $reply .= $line;
    if (substr($line, 3, 1) == ' ') break;
}
echo "AUTH: " . trim($reply) . "\n";

fputs($handle, base64_encode($username) . "\r\n");
$reply = '';
while ($line = fgets($handle, 515)) {
    $reply .= $line;
    if (substr($line, 3, 1) == ' ') break;
}

fputs($handle, base64_encode($password) . "\r\n");
$reply = '';
while ($line = fgets($handle, 515)) {
    $reply .= $line;
    if (substr($line, 3, 1) == ' ') break;
}
echo "Auth result: " . trim($reply) . "\n";

if (substr($reply, 0, 3) == '235') {
    echo "SUCCESS: SMTP auth works. Use tls://smtp.gmail.com in OpenCart Mail settings.\n";
} else {
    echo "FAIL: Auth rejected. Use a Gmail App Password (not regular password).\n";
}

fclose($handle);
