<?php

// Load configuration
$config = require_once 'config.php';

/**
 * Function kutuma message WhatsApp
 */
function sendWhatsAppMessage($to, $message) {
    global $config;
    
    $url = $config['api_url'] . '/messages/send';
    
    $data = [
        'instance_id' => $config['instance_id'],
        'to' => $to,
        'message' => $message
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $config['api_key'],
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        return ['success' => false, 'error' => curl_error($ch)];
    }
    
    curl_close($ch);
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

// Mfano wa matumizi (Uncomment ili kutest)
/*
$phoneNumber = '255712345678'; // Namba ya mpokeaji
$message = 'Habari! Hii ni test message kutoka kwenye PHP integration.';

$result = sendWhatsAppMessage($phoneNumber, $message);

if ($result['success']) {
    echo "Message imetumwa kikamilifu!\n";
    print_r($result['response']);
} else {
    echo "Imeshindikana kutuma message.\n";
    print_r($result);
}
*/
