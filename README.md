# SKY WhatsApp Integration

System rahisi ya PHP kwa kutuma na kupokea ujumbe wa WhatsApp.

## Structure ya Folder

```
integration_examples/
├── index.php          # Dashboard kuu (UI)
├── api.php            # Functions za kutuma/kupokea
├── config.php         # Settings (API Key, Instance ID)
├── webhook.php        # Kupokea ujumbe kutoka API
├── assets/
│   ├── style.css      # Styles za UI
│   └── app.js         # JavaScript
└── data/              # Storage ya messages (auto-created)
```

## Jinsi ya Kutumia

### 1. Set Configuration
Fungua `config.php` na uweke:
- `api_key` - API Key yako
- `instance_id` - Instance ID yako (angalia dashboard)

### 2. Run Locally
```bash
cd integration_examples
php -S localhost:8080
```
Kisha fungua: http://localhost:8080

### 3. Set Webhook (Kupokea Messages)

1. Host folder hii kwenye server yako (au tumia Ngrok kwa testing)
2. Nenda Dashboard: https://orange.ericksky.online
3. Unda Webhook mpya:
   - URL: `https://your-domain.com/webhook.php`
   - Events: `message.inbound`

## Features

✅ Kutuma WhatsApp messages  
✅ Kupokea messages via webhook  
✅ Auto-reply kwa keywords  
✅ Beautiful dark theme UI  
✅ Real-time updates  

## Customization

### Kuongeza Auto-Replies
Fungua `webhook.php` na edit array ya `$replies`:

```php
$replies = [
    'habari' => 'Habari! Karibu...',
    'bei' => 'Bei zetu ni...',
    // Ongeza zako hapa
];
```
