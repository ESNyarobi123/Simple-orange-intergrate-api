<?php
/**
 * Quick API Test - Debug 404 Error
 */
header('Content-Type: text/html; charset=utf-8');

$config = require __DIR__ . '/config.php';

echo "<h2>🔧 API Connection Test</h2>";
echo "<pre style='background:#1a1a2e;color:#eee;padding:20px;border-radius:10px;'>";

// Config check
echo "<strong style='color:#00d9a5'>1. Configuration:</strong>\n";
echo "   API URL: " . $config['api_url'] . "\n";
echo "   API Key: " . substr($config['api_key'], 0, 15) . "...\n";
echo "   Instance ID: " . $config['instance_id'] . "\n\n";

// Test the actual API call
echo "<strong style='color:#00d9a5'>2. Testing API Call:</strong>\n";

$url = $config['api_url'] . '/messages/send';
$data = [
    'instance_id' => (int) $config['instance_id'],
    'to' => '255712345678', // Test number
    'body' => 'Test message from debug script'
];

echo "   URL: $url\n";
echo "   Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $config['api_key'],
        'Content-Type: application/json',
        'Accept: application/json'
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_VERBOSE => true, // Enable verbose for debugging
    CURLOPT_HEADER => true,  // Include headers in output
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);
$error = curl_error($ch);
$errno = curl_errno($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "<strong style='color:#00d9a5'>3. Response:</strong>\n";
echo "   HTTP Code: ";
if ($httpCode >= 200 && $httpCode < 300) {
    echo "<span style='color:#00d9a5'>$httpCode ✓</span>\n";
} elseif ($httpCode == 404) {
    echo "<span style='color:#ea3943'>$httpCode ✗ NOT FOUND</span>\n";
} elseif ($httpCode == 401) {
    echo "<span style='color:#ea3943'>$httpCode ✗ UNAUTHORIZED (Invalid API Key?)</span>\n";
} else {
    echo "<span style='color:#fcd535'>$httpCode</span>\n";
}

if ($error) {
    echo "   <span style='color:#ea3943'>cURL Error: $error (Code: $errno)</span>\n";
}

echo "\n<strong style='color:#00d9a5'>4. Response Headers:</strong>\n";
echo htmlspecialchars($headers) . "\n";

echo "<strong style='color:#00d9a5'>5. Response Body:</strong>\n";
$decoded = json_decode($body, true);
if ($decoded) {
    echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} else {
    echo htmlspecialchars($body) . "\n";
}

// Check possible causes
echo "\n<strong style='color:#fcd535'>6. Possible Causes for 404:</strong>\n";
if ($httpCode == 404) {
    echo "   ⚠️  Check if Instance ID $config[instance_id] exists and belongs to you\n";
    echo "   ⚠️  Check if Instance is CONNECTED (status = 'connected')\n";
    echo "   ⚠️  Check if API Key is valid and not expired\n";
    echo "   ⚠️  Verify URL is correct: $url\n";
    
    if (isset($decoded['error']['code'])) {
        echo "\n   <strong>Error Code:</strong> " . $decoded['error']['code'] . "\n";
        echo "   <strong>Error Message:</strong> " . ($decoded['error']['message'] ?? 'N/A') . "\n";
    }
}

echo "</pre>";

// Show a simple test form
echo "
<h3>📤 Quick Send Test</h3>
<form method='POST' action=''>
    <input type='hidden' name='action' value='test_send'>
    <label>Phone Number: <input type='text' name='to' value='255' style='padding:8px;width:200px'></label><br><br>
    <label>Message: <input type='text' name='message' value='Test from debug' style='padding:8px;width:300px'></label><br><br>
    <button type='submit' style='padding:10px 20px;background:#00d9a5;color:white;border:none;border-radius:5px;cursor:pointer'>Send Test Message</button>
</form>
";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'test_send') {
    require_once __DIR__ . '/api.php';
    $result = sendWhatsAppMessage($_POST['to'] ?? '', $_POST['message'] ?? '');
    echo "<h4>Result:</h4>";
    echo "<pre style='background:#1a1a2e;color:#eee;padding:15px;border-radius:8px'>";
    print_r($result);
    echo "</pre>";
}
?>
