/**
 * SKY WhatsApp Integration - JavaScript
 */

// Modal Functions
function openSendModal() {
    document.getElementById('sendModal').classList.add('active');
    document.getElementById('to').focus();
}

function closeSendModal() {
    document.getElementById('sendModal').classList.remove('active');
    document.getElementById('sendForm').reset();
}

// Close modal on overlay click
document.getElementById('sendModal').addEventListener('click', function (e) {
    if (e.target === this) {
        closeSendModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeSendModal();
    }
});

// Send Message
async function sendMessage(e) {
    e.preventDefault();

    const btn = document.getElementById('sendBtn');
    const btnText = btn.querySelector('.btn-text');
    const btnLoader = btn.querySelector('.btn-loader');

    const to = document.getElementById('to').value;
    const message = document.getElementById('message').value;

    // Disable button
    btn.disabled = true;
    btnText.style.display = 'none';
    btnLoader.style.display = 'inline';

    try {
        const formData = new FormData();
        formData.append('action', 'send');
        formData.append('to', to);
        formData.append('message', message);

        const response = await fetch('index.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showToast('Ujumbe umetumwa kikamilifu!', 'success');
            closeSendModal();
            refreshMessages();
        } else {
            const errorMsg = result.error || result.raw || 'Imeshindikana kutuma ujumbe';
            const httpCode = result.http_code ? ` (HTTP ${result.http_code})` : '';
            showToast(errorMsg + httpCode, 'error');
            console.error('Send error:', result);
        }
    } catch (error) {
        showToast('Kuna tatizo la mtandao', 'error');
        console.error(error);
    } finally {
        btn.disabled = false;
        btnText.style.display = 'inline';
        btnLoader.style.display = 'none';
    }
}

// Refresh Messages
async function refreshMessages() {
    try {
        const formData = new FormData();
        formData.append('action', 'get_messages');

        const response = await fetch('index.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            renderMessages(result.messages);
        }
    } catch (error) {
        console.error('Failed to refresh messages:', error);
    }
}

// Render Messages
function renderMessages(messages) {
    const container = document.getElementById('messagesContainer');

    if (!messages || messages.length === 0) {
        container.innerHTML = `
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
        `;
        return;
    }

    container.innerHTML = messages.map(msg => `
        <div class="message-card ${msg.direction}">
            <div class="message-avatar">
                ${msg.direction === 'inbound' ? '👤' : '🤖'}
            </div>
            <div class="message-content">
                <div class="message-header">
                    <span class="message-from">${escapeHtml(formatPhone(msg.from))}</span>
                    <span class="message-arrow">→</span>
                    <span class="message-to">${escapeHtml(formatPhone(msg.to))}</span>
                    <span class="message-time">${timeAgo(msg.timestamp)}</span>
                </div>
                <div class="message-body">${escapeHtml(msg.body).replace(/\n/g, '<br>')}</div>
                <div class="message-footer">
                    <span class="message-status ${msg.status}">
                        ${msg.direction === 'outbound'
            ? (msg.status === 'sent' ? '✓ Imetumwa' : (msg.status === 'delivered' ? '✓✓ Imefika' : '⏳ Inatuma...'))
            : '📥 Imepokelewa'
        }
                    </span>
                </div>
            </div>
        </div>
    `).join('');
}

// Toast Notification
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = 'toast ' + type + ' show';

    setTimeout(() => {
        toast.classList.remove('show');
    }, 4000);
}

// Helper Functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatPhone(phone) {
    if (!phone) return phone;
    phone = phone.replace(/\D/g, '');
    if (phone.length >= 12) {
        return '+' + phone.substring(0, 3) + ' ' + phone.substring(3, 6) + ' ' + phone.substring(6, 9) + ' ' + phone.substring(9);
    }
    return phone;
}

function timeAgo(datetime) {
    const now = new Date();
    const date = new Date(datetime);
    const diff = Math.floor((now - date) / 1000);

    if (diff < 60) return 'Sasa hivi';
    if (diff < 3600) return Math.floor(diff / 60) + ' dakika zilizopita';
    if (diff < 86400) return Math.floor(diff / 3600) + ' saa zilizopita';
    if (diff < 604800) return Math.floor(diff / 86400) + ' siku zilizopita';
    return date.toLocaleDateString('sw-TZ');
}

// Auto-refresh every 30 seconds
setInterval(refreshMessages, 30000);

// Initial animation
document.addEventListener('DOMContentLoaded', function () {
    const cards = document.querySelectorAll('.message-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
