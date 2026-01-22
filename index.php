<?php
/**
 * SKY WhatsApp Integration - Dashboard
 */
require_once __DIR__ . '/api.php';

$config = require __DIR__ . '/config.php';
$messages = getMessages(50);

// Handle AJAX send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'send') {
        $to = $_POST['to'] ?? '';
        $message = $_POST['message'] ?? '';
        
        if (empty($to) || empty($message)) {
            echo json_encode(['success' => false, 'error' => 'Jaza namba na ujumbe']);
            exit;
        }
        
        $result = sendWhatsAppMessage($to, $message);
        
        if ($result['success']) {
            saveMessage([
                'from' => 'Me',
                'to' => $to,
                'body' => $message,
                'direction' => 'outbound',
                'status' => 'sent',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
        
        echo json_encode($result);
        exit;
    }
    
    if ($_POST['action'] === 'get_messages') {
        echo json_encode(['success' => true, 'messages' => getMessages(50)]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($config['app_name']) ?> - Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="app">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <div class="logo-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </div>
                    <span class="logo-text"><?= htmlspecialchars($config['app_name']) ?></span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="#" class="nav-item active">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    <span>Messages</span>
                </a>
                <a href="#" class="nav-item" onclick="openSendModal(); return false;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                    <span>Tuma Ujumbe</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <div class="status-indicator online"></div>
                <span>Instance Connected</span>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <div class="header-left">
                    <h1>Messages</h1>
                    <p class="subtitle">Ujumbe wote wa WhatsApp</p>
                </div>
                <div class="header-right">
                    <button class="btn btn-primary" onclick="openSendModal()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Tuma Ujumbe Mpya
                    </button>
                    <button class="btn btn-ghost" onclick="refreshMessages()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/>
                            <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                        </svg>
                    </button>
                </div>
            </header>
            
            <div class="messages-container" id="messagesContainer">
                <?php if (empty($messages)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </div>
                    <h3>Hakuna Ujumbe Bado</h3>
                    <p>Ujumbe unaoingia utaonekana hapa. Anza kwa kutuma ujumbe!</p>
                    <button class="btn btn-primary" onclick="openSendModal()">Tuma Ujumbe wa Kwanza</button>
                </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                    <div class="message-card <?= $msg['direction'] ?>">
                        <div class="message-avatar">
                            <?= $msg['direction'] === 'inbound' ? '👤' : '🤖' ?>
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-from"><?= htmlspecialchars(formatPhone($msg['from'])) ?></span>
                                <span class="message-arrow">→</span>
                                <span class="message-to"><?= htmlspecialchars(formatPhone($msg['to'])) ?></span>
                                <span class="message-time"><?= timeAgo($msg['timestamp']) ?></span>
                            </div>
                            <div class="message-body"><?= nl2br(htmlspecialchars($msg['body'])) ?></div>
                            <div class="message-footer">
                                <span class="message-status <?= $msg['status'] ?>">
                                    <?php if ($msg['direction'] === 'outbound'): ?>
                                        <?= $msg['status'] === 'sent' ? '✓ Imetumwa' : ($msg['status'] === 'delivered' ? '✓✓ Imefika' : '⏳ Inatuma...') ?>
                                    <?php else: ?>
                                        📥 Imepokelewa
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Send Message Modal -->
    <div class="modal-overlay" id="sendModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Tuma Ujumbe</h2>
                <button class="modal-close" onclick="closeSendModal()">×</button>
            </div>
            <form id="sendForm" onsubmit="sendMessage(event)">
                <div class="form-group">
                    <label for="to">Namba ya Simu</label>
                    <input type="text" id="to" name="to" placeholder="Mfano: 0712345678 au 255712345678" required>
                </div>
                <div class="form-group">
                    <label for="message">Ujumbe</label>
                    <textarea id="message" name="message" rows="4" placeholder="Andika ujumbe wako hapa..." required></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-ghost" onclick="closeSendModal()">Ghairi</button>
                    <button type="submit" class="btn btn-primary" id="sendBtn">
                        <span class="btn-text">Tuma Ujumbe</span>
                        <span class="btn-loader" style="display:none;">Inatuma...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Toast Notification -->
    <div class="toast" id="toast"></div>
    
    <script src="assets/app.js"></script>
</body>
</html>
