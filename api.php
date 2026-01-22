<?php
/**
 * SKY WhatsApp Integration - API Helper Functions
 */

$config = require __DIR__ . '/config.php';

/**
 * Send WhatsApp Message via API
 */
function sendWhatsAppMessage($to, $message, $metadata = []) {
    global $config;
    
    // Clean phone number - remove spaces, dashes, and leading zeros
    $to = preg_replace('/[^0-9]/', '', $to);
    if (strpos($to, '0') === 0) {
        $to = '255' . substr($to, 1); // Tanzania default
    }
    
    $url = $config['api_url'] . '/messages/send';
    
    $data = [
        'instance_id' => (int) $config['instance_id'],
        'to' => $to,
        'body' => $message
    ];
    
    if (!empty($metadata)) {
        $data['metadata'] = $metadata;
    }
    
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
        CURLOPT_SSL_VERIFYHOST => 0
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    curl_close($ch);
    
    if ($error || $errno) {
        return [
            'success' => false, 
            'error' => "cURL Error ($errno): $error",
            'http_code' => $httpCode,
            'debug' => [
                'url' => $url,
                'data' => $data,
            ]
        ];
    }
    
    $decoded = json_decode($response, true);
    
    // Better error message extraction
    $errorMessage = 'Unknown error';
    if ($httpCode === 404) {
        $errorMessage = $decoded['error']['message'] ?? 'API endpoint not found or Instance not found (HTTP 404)';
    } elseif ($httpCode === 401) {
        $errorMessage = 'Invalid API Key (HTTP 401)';
    } elseif ($httpCode === 403) {
        $errorMessage = $decoded['error']['message'] ?? 'Access denied (HTTP 403)';
    } elseif ($httpCode === 400) {
        $errorMessage = $decoded['error']['message'] ?? 'Bad request (HTTP 400)';
    } elseif (isset($decoded['error']['message'])) {
        $errorMessage = $decoded['error']['message'];
    } elseif (isset($decoded['message'])) {
        $errorMessage = $decoded['message'];
    }
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'response' => $decoded,
        'error' => $httpCode >= 200 && $httpCode < 300 ? null : "$errorMessage (HTTP $httpCode)",
        'raw' => $response,
        'debug' => [
            'url' => $url,
            'instance_id' => (int) $config['instance_id'],
            'api_key_prefix' => substr($config['api_key'], 0, 10) . '...',
        ]
    ];
}


/**
 * Get Messages from local storage
 */
function getMessages($limit = 50) {
    $file = __DIR__ . '/data/messages.json';
    if (!file_exists($file)) return [];
    
    $messages = json_decode(file_get_contents($file), true) ?? [];
    usort($messages, fn($a, $b) => strtotime($b['timestamp']) - strtotime($a['timestamp']));
    return array_slice($messages, 0, $limit);
}

/**
 * Save Message to local storage
 */
function saveMessage($data) {
    $dir = __DIR__ . '/data';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    
    $file = $dir . '/messages.json';
    $messages = file_exists($file) ? json_decode(file_get_contents($file), true) ?? [] : [];
    
    $message = [
        'id' => uniqid('msg_'),
        'from' => $data['from'] ?? 'Unknown',
        'to' => $data['to'] ?? 'Unknown',
        'body' => $data['body'] ?? $data['message'] ?? '',
        'direction' => $data['direction'] ?? 'inbound',
        'status' => $data['status'] ?? 'received',
        'timestamp' => $data['timestamp'] ?? date('Y-m-d H:i:s')
    ];
    
    $messages[] = $message;
    
    // Keep only last 500 messages
    if (count($messages) > 500) {
        $messages = array_slice($messages, -500);
    }
    
    file_put_contents($file, json_encode($messages, JSON_PRETTY_PRINT));
    return $message;
}

/**
 * Format phone number for display
 */
function formatPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) >= 12) {
        return '+' . substr($phone, 0, 3) . ' ' . substr($phone, 3, 3) . ' ' . substr($phone, 6, 3) . ' ' . substr($phone, 9);
    }
    return $phone;
}

/**
 * Time ago helper
 */
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->y > 0) return $diff->y . ' mwaka' . ($diff->y > 1 ? '' : '') . ' uliopita';
    if ($diff->m > 0) return $diff->m . ' mwezi' . ($diff->m > 1 ? '' : '') . ' uliopita';
    if ($diff->d > 0) return $diff->d . ' siku' . ($diff->d > 1 ? '' : '') . ' zilizopita';
    if ($diff->h > 0) return $diff->h . ' saa' . ($diff->h > 1 ? '' : '') . ' zilizopita';
    if ($diff->i > 0) return $diff->i . ' dakika' . ($diff->i > 1 ? '' : '') . ' zilizopita';
    return 'Sasa hivi';
}
