<?php
/**
 * SKY WhatsApp Integration - Webhook Handler
 * ===========================================
 * Hii file inapokea messages kutoka WhatsApp API
 * Weka URL hii kwenye Dashboard: https://your-domain.com/webhook.php
 */

require_once __DIR__ . '/api.php';

// Enable CORS for testing
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Webhook-Signature');
header('Content-Type: application/json');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get payload
$input = file_get_contents('php://input');
$payload = json_decode($input, true);

// Log webhook (for debugging)
$logDir = __DIR__ . '/data';
if (!is_dir($logDir)) mkdir($logDir, 0755, true);
file_put_contents($logDir . '/webhook.log', date('Y-m-d H:i:s') . " - " . $input . "\n", FILE_APPEND);

// Process message.inbound event
if (isset($payload['event']) && $payload['event'] === 'message.inbound') {
    $data = $payload['data'] ?? [];
    
    // Save to local storage
    saveMessage([
        'from' => $data['from'] ?? 'Unknown',
        'to' => $data['to'] ?? 'Instance',
        'body' => $data['body'] ?? $data['message'] ?? '',
        'direction' => 'inbound',
        'status' => 'received',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    // Auto-Reply Logic (Mfano - unaweza kubadilisha)
    $message = strtolower(trim($data['body'] ?? ''));
    $from = $data['from'] ?? '';
    
    $replies = [
        'habari' => 'Habari! Karibu kwenye huduma yetu. Tunawezaje kukusaidia leo?',
        'hello' => 'Hello! Welcome to our service. How can we help you today?',
        'hi' => 'Hi there! 👋 How can I assist you?',
        'help' => "Huduma zetu:\n1. Tuma 'bei' - Kuona bei zetu\n2. Tuma 'mawasiliano' - Kupata contact\n3. Tuma 'msaada' - Kuzungumza na mtaalamu"
    ];
    
    if (isset($replies[$message]) && $from) {
        sendWhatsAppMessage($from, $replies[$message]);
        
        // Log outbound reply
        saveMessage([
            'from' => 'Bot',
            'to' => $from,
            'body' => $replies[$message],
            'direction' => 'outbound',
            'status' => 'sent',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}

// Process message.status event
if (isset($payload['event']) && $payload['event'] === 'message.status') {
    // Update message status (delivered, read, failed)
    file_put_contents($logDir . '/status.log', date('Y-m-d H:i:s') . " - " . $input . "\n", FILE_APPEND);
}

// Return success
echo json_encode(['success' => true, 'message' => 'Webhook received']);
