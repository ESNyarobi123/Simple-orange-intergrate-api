<?php
/**
 * Quick API Test - Angalia kama API inafanya kazi
 */

require_once __DIR__ . '/api.php';

header('Content-Type: application/json');

echo "=== API Connection Test ===\n\n";

// Test 1: Check config
$config = require __DIR__ . '/config.php';
echo "API URL: " . $config['api_url'] . "\n";
echo "Instance ID: " . $config['instance_id'] . "\n";
echo "API Key: " . substr($config['api_key'], 0, 20) . "...\n\n";

// Test 2: Try to send a test message
echo "=== Testing Message Send ===\n";

$result = sendWhatsAppMessage('255712345678', 'Test message from API');

echo "Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
echo "HTTP Code: " . ($result['http_code'] ?? 'N/A') . "\n";
echo "Error: " . ($result['error'] ?? 'None') . "\n";
echo "Raw Response: " . ($result['raw'] ?? 'N/A') . "\n\n";

echo "=== Full Result ===\n";
print_r($result);
