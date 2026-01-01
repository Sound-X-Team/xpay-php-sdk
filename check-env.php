<?php
echo "Environment Variables:\n";
echo "XPAY_TEST_API_KEY: " . ($_ENV['XPAY_TEST_API_KEY'] ?? 'NOT SET') . "\n";
echo "XPAY_MERCHANT_ID: " . ($_ENV['XPAY_MERCHANT_ID'] ?? 'NOT SET') . "\n";

// Also check $_SERVER
echo "\nServer Variables:\n";
echo "XPAY_TEST_API_KEY: " . ($_SERVER['XPAY_TEST_API_KEY'] ?? 'NOT SET') . "\n";
echo "XPAY_MERCHANT_ID: " . ($_SERVER['XPAY_MERCHANT_ID'] ?? 'NOT SET') . "\n";

echo "\nAll Environment Variables:\n";
foreach ($_ENV as $key => $value) {
    if (str_contains($key, 'XPAY')) {
        echo "$key: $value\n";
    }
}
?>