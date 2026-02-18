<?php
/**
 * Contract deliverables – file presence check.
 * Run from project root: php verify-deliverables.php
 */
$root = dirname(__DIR__) . '/';
if (!is_dir($root . 'catalog')) {
    $root = __DIR__ . '/';
}

$checks = [
    'Platform audit' => ['audit-platform.php'],
    'Forgotten password' => ['catalog/controller/account/forgotten.php'],
    'Email notification manager' => ['system/library/email_notification_manager.php'],
    'Product visibility' => ['system/library/product_visibility_helper.php', 'admin/controller/catalog/product.php'],
    'Discount helper' => ['system/library/discount_helper.php'],
    'Analytics helper' => ['system/library/analytics_helper.php'],
    'SEO helper' => ['system/library/seo_helper.php'],
    'Tax exemption' => ['system/library/tax_exemption_helper.php', 'catalog/model/extension/total/tax.php'],
    'Multi-seller shipping helper' => ['system/library/multi_vendor_shipping_helper.php'],
    'Minimum order (checkout)' => ['catalog/controller/checkout/checkout.php'],
    'Reseller signup' => ['catalog/controller/account/register.php'],
    'Bulk upload (admin)' => ['admin/controller/extension/purpletree_multivendor/bulkproductupload.php'],
    'Bulk upload (seller)' => ['catalog/controller/extension/account/purpletree_multivendor/bulkproductupload.php'],
    'Onboarding checkpoints' => ['catalog/controller/extension/account/purpletree_multivendor/dashboardicons.php'],
    'Cache clear (admin)' => ['admin/controller/tool/cache_clear.php'],
];

echo "Contract deliverables – file presence\n";
echo str_repeat('-', 50) . "\n";

$ok = 0;
$fail = 0;
foreach ($checks as $name => $files) {
    $all = true;
    foreach ($files as $f) {
        if (!is_file($root . $f)) {
            $all = false;
            break;
        }
    }
    if ($all) {
        echo "[OK] $name\n";
        $ok++;
    } else {
        echo "[--] $name (missing: " . implode(', ', $files) . ")\n";
        $fail++;
    }
}

$docs = ['DELIVERABLES-DOCUMENTATION.md', 'REGRESSION-TEST-CHECKLIST.md', 'MASS-IMPORT-INSTRUCTIONS.md', 'VARIANTS-OPTIONS-PRICING.md', 'STRIPE-INTEGRATION-NOTES.md', 'AI-FEASIBILITY.md', 'DISCOUNT-INSTRUCTIONS.md', 'CONTRACT-REQUIREMENTS-TEST.md'];
echo "\nDocumentation:\n";
foreach ($docs as $d) {
    $exists = is_file($root . $d);
    echo ($exists ? '[OK] ' : '[--] ') . $d . "\n";
    if ($exists) $ok++; else $fail++;
}

echo "\n" . str_repeat('-', 50) . "\n";
echo "Summary: $ok present, $fail missing/not found\n";
exit($fail > 0 ? 1 : 0);
