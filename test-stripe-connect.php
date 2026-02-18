<?php
/**
 * Standalone test for Stripe Connect OAuth token exchange.
 * Run from CLI or browser to verify client_id + secret_key match.
 *
 * Usage:
 *   CLI: php test-stripe-connect.php [--code=ac_xxx] [--client_id=ca_xxx] [--secret=sk_test_xxx]
 *   Browser: test-stripe-connect.php?code=ac_xxx  (and set config below or use env)
 *
 * Get a code: Click Connect on seller dashboard, complete Stripe flow; before redirect
 * copy the "code" from the URL (index.php?route=...&code=ac_xxxxx) or enable debug below.
 *
 * Config: set STRIPE_CLIENT_ID_TEST and STRIPE_SECRET_KEY_TEST in env, or edit below.
 */
$client_id = getenv('STRIPE_CLIENT_ID_TEST') ?: '';
$secret_key = getenv('STRIPE_SECRET_KEY_TEST') ?: '';
$code = '';

if (php_sapi_name() === 'cli') {
    for ($i = 1; $i < $argc; $i++) {
        if (strpos($argv[$i], '--code=') === 0) {
            $code = substr($argv[$i], 7);
        } elseif (strpos($argv[$i], '--client_id=') === 0) {
            $client_id = substr($argv[$i], 12);
        } elseif (strpos($argv[$i], '--secret=') === 0) {
            $secret_key = substr($argv[$i], 9);
        }
    }
} else {
    $code = isset($_GET['code']) ? trim($_GET['code']) : '';
    if (isset($_GET['client_id'])) {
        $client_id = trim($_GET['client_id']);
    }
    if (isset($_GET['secret'])) {
        $secret_key = trim($_GET['secret']);
    }
}

// Optional: load from a local config file (create stripe-connect-test-config.php with $stripe_client_id_test and $stripe_secret_key_test)
if (($client_id === '' || $secret_key === '') && is_file(__DIR__ . '/stripe-connect-test-config.php')) {
    include __DIR__ . '/stripe-connect-test-config.php';
    if (!empty($stripe_client_id_test)) {
        $client_id = $stripe_client_id_test;
    }
    if (!empty($stripe_secret_key_test)) {
        $secret_key = $stripe_secret_key_test;
    }
}

header('Content-Type: text/plain; charset=utf-8');

echo "Stripe Connect token exchange test\n";
echo str_repeat('-', 50) . "\n";

if ($secret_key === '') {
    echo "ERROR: No secret key. Set STRIPE_SECRET_KEY_TEST env or --secret=sk_test_xxx or run from OpenCart root.\n";
    exit(1);
}
if ($client_id === '') {
    echo "ERROR: No client ID. Set STRIPE_CLIENT_ID_TEST env or --client_id=ca_xxx.\n";
    exit(1);
}

echo "Client ID: " . substr($client_id, 0, 20) . "...\n";
echo "Secret key: " . substr($secret_key, 0, 20) . "...\n\n";

// 1) Verify secret key with Stripe API (proves key is valid)
echo "1) Testing secret key with Stripe API (GET /v1/account)...\n";
$ch = curl_init('https://api.stripe.com/v1/account');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $secret_key]);
$body = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($http === 200 && strpos($body, '"id":') !== false) {
    $acc = json_decode($body, true);
    echo "   OK. Account id: " . (isset($acc['id']) ? $acc['id'] : 'unknown') . "\n\n";
} else {
    echo "   FAIL. HTTP $http. Body: " . substr($body, 0, 300) . "\n";
    if ($http === 401) {
        echo "   -> Secret key is invalid or revoked. Copy it again from Stripe Dashboard (Developers -> API keys -> Test -> Reveal).\n";
    }
    echo "\n";
}

// 2) If code provided, exchange it for token (proves client_id + secret belong together)
if ($code !== '') {
    echo "2) Exchanging authorization code for token...\n";
    $ch = curl_init('https://connect.stripe.com/oauth/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_secret' => $secret_key,
        'code'          => $code,
        'grant_type'    => 'authorization_code',
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    $body = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $result = json_decode($body);
    if ($http === 200 && isset($result->stripe_user_id)) {
        echo "   OK. Connected account id: " . $result->stripe_user_id . "\n";
        echo "   livemode: " . (isset($result->livemode) ? ($result->livemode ? 'true' : 'false') : 'n/a') . "\n\n";
    } else {
        echo "   FAIL. HTTP $http\n";
        echo "   " . (isset($result->error_description) ? $result->error_description : $body) . "\n\n";
    }
} else {
    echo "2) No code provided. To test token exchange:\n";
    echo "   - Click Connect on seller dashboard, complete Stripe flow.\n";
    echo "   - Before the redirect finishes, copy the 'code' from the URL (code=ac_xxx).\n";
    echo "   - Run: php test-stripe-connect.php --code=ac_xxx --client_id=" . substr($client_id, 0, 15) . "... --secret=sk_test_xxx\n";
    echo "   - Or open: test-stripe-connect.php?code=ac_xxx (and set client_id/secret in env or OpenCart config)\n\n";
}

echo "Done.\n";
