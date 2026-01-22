<?php
/**
 * DETAILED API DEBUG TEST
 * Angalia exactly nini kinashindikana
 */

$config = require __DIR__ . '/config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>API Debug Test</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #00ff00; padding: 20px; }
        .section { background: #2a2a2a; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .error { color: #ff4444; }
        .success { color: #44ff44; }
        .warning { color: #ffaa00; }
        pre { background: #000; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
<h1>🔍 API Debug Test</h1>
";

// TEST 1: Configuration
echo "<div class='section'>";
echo "<h2>1️⃣ Configuration Check</h2>";
echo "<table>";
echo "<tr><td><b>API URL:</b></td><td>" . htmlspecialchars($config['api_url']) . "</td></tr>";
echo "<tr><td><b>Instance ID:</b></td><td>" . htmlspecialchars($config['instance_id']) . "</td></tr>";
echo "<tr><td><b>API Key:</b></td><td>" . htmlspecialchars(substr($config['api_key'], 0, 25)) . "...</td></tr>";
echo "</table>";
echo "</div>";

// TEST 2: Check if server is reachable
echo "<div class='section'>";
echo "<h2>2️⃣ Server Reachability Test</h2>";
$pingUrl = str_replace('/api/v1', '', $config['api_url']);
echo "<p>Testing: <b>" . htmlspecialchars($pingUrl) . "</b></p>";

$ch = curl_init($pingUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "<p class='error'>❌ Server NOT reachable: " . htmlspecialchars($error) . "</p>";
} else {
    echo "<p class='success'>✅ Server is reachable (HTTP $httpCode)</p>";
}
echo "</div>";

// TEST 3: Check instance status endpoint
echo "<div class='section'>";
echo "<h2>3️⃣ Instance Status Check</h2>";
$statusUrl = $config['api_url'] . '/instances/' . $config['instance_id'];
echo "<p>Checking: <b>" . htmlspecialchars($statusUrl) . "</b></p>";

$ch = curl_init($statusUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $config['api_key'],
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<p><b>HTTP Code:</b> <span class='" . ($httpCode == 200 ? 'success' : 'error') . "'>$httpCode</span></p>";

if ($error) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($error) . "</p>";
} else {
    $data = json_decode($response, true);
    echo "<p><b>Response:</b></p>";
    echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";
    
    if (isset($data['status'])) {
        $status = $data['status'];
        if ($status === 'connected') {
            echo "<p class='success'>✅ Instance is CONNECTED!</p>";
        } else {
            echo "<p class='warning'>⚠️ Instance status: " . htmlspecialchars($status) . "</p>";
        }
    }
}
echo "</div>";

// TEST 4: Try sending a message
echo "<div class='section'>";
echo "<h2>4️⃣ Send Message Test</h2>";
$sendUrl = $config['api_url'] . '/messages/send';
echo "<p>Endpoint: <b>" . htmlspecialchars($sendUrl) . "</b></p>";

$payload = [
    'instance_id' => (int) $config['instance_id'],
    'to' => '255712345678',
    'body' => 'Test message - ' . date('H:i:s')
];

echo "<p><b>Payload:</b></p>";
echo "<pre>" . htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT)) . "</pre>";

$ch = curl_init($sendUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $config['api_key'],
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
$curlInfo = curl_getinfo($ch);
curl_close($ch);

echo "<p><b>HTTP Code:</b> <span class='" . ($httpCode >= 200 && $httpCode < 300 ? 'success' : 'error') . "'>$httpCode</span></p>";

if ($error) {
    echo "<p class='error'>❌ cURL Error: " . htmlspecialchars($error) . "</p>";
}

echo "<p><b>Response:</b></p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

$data = json_decode($response, true);
if ($data) {
    echo "<p><b>Parsed Response:</b></p>";
    echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";
}

// Diagnosis
echo "<hr>";
if ($httpCode == 404) {
    echo "<h3 class='error'>🔴 DIAGNOSIS: HTTP 404 - NOT FOUND</h3>";
    echo "<p><b>Possible causes:</b></p>";
    echo "<ul>";
    echo "<li>API endpoint haipo (<code>/messages/send</code> route not registered)</li>";
    echo "<li>Instance ID <b>" . htmlspecialchars($config['instance_id']) . "</b> haipo kwenye database</li>";
    echo "<li>API version mismatch (expecting <code>/api/v1</code>)</li>";
    echo "</ul>";
    echo "<p><b>Suggested fixes:</b></p>";
    echo "<ol>";
    echo "<li>Confirm Instance 11 iko kwenye dashboard: <a href='https://orange.ericksky.online/login' target='_blank'>Check here</a></li>";
    echo "<li>Check API routes kwenye backend (routes/api.php)</li>";
    echo "<li>Jaribu endpoint nyingine kama <code>/instances</code> to test API key</li>";
    echo "</ol>";
} elseif ($httpCode >= 200 && $httpCode < 300) {
    echo "<h3 class='success'>✅ SUCCESS! Message sent successfully!</h3>";
} else {
    echo "<h3 class='warning'>⚠️ ERROR: HTTP $httpCode</h3>";
}

echo "</div>";

// TEST 5: List all instances
echo "<div class='section'>";
echo "<h2>5️⃣ List All Instances</h2>";
$listUrl = $config['api_url'] . '/instances';
echo "<p>Endpoint: <b>" . htmlspecialchars($listUrl) . "</b></p>";

$ch = curl_init($listUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $config['api_key'],
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><b>HTTP Code:</b> $httpCode</p>";
echo "<p><b>Response:</b></p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

$data = json_decode($response, true);
if ($data && isset($data['instances'])) {
    echo "<p class='success'>Found " . count($data['instances']) . " instance(s)</p>";
    echo "<ul>";
    foreach ($data['instances'] as $inst) {
        $highlight = ($inst['id'] == $config['instance_id']) ? 'background: yellow; color: black;' : '';
        echo "<li style='$highlight'>";
        echo "ID: <b>" . $inst['id'] . "</b> | ";
        echo "Status: <b>" . ($inst['status'] ?? 'unknown') . "</b> | ";
        echo "Name: " . ($inst['name'] ?? 'N/A');
        if ($inst['id'] == $config['instance_id']) {
            echo " <b>← YOUR INSTANCE</b>";
        }
        echo "</li>";
    }
    echo "</ul>";
}
echo "</div>";

echo "<hr><p><small>Debug completed at " . date('Y-m-d H:i:s') . "</small></p>";
echo "</body></html>";
