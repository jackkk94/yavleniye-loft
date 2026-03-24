<?php

header('Content-Type: application/json');

$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Config file not found']);
    exit;
}

$config = require $configPath;
$botToken = $config['telegram_bot_token'] ?? '';
$chatIds = $config['telegram_chat_ids'] ?? [];

if (empty($botToken) || empty($chatIds)) {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid configuration']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$name = htmlspecialchars($data['name'] ?? '');
$phone = htmlspecialchars($data['phone'] ?? '');
$date = htmlspecialchars($data['date'] ?? '');
$comments = htmlspecialchars($data['comments'] ?? '');

$isTariffWithEquipment = $data['isTariffWithEquipment'] ?? false;
$tariff = $isTariffWithEquipment ? 'С оборудованием' : 'Без оборудования';

$rentTime = $data['rentTime'] ?? ['hours' => 0, 'price' => 0];
$techTime = $data['techTime'] ?? ['hours' => 0, 'price' => 0];

$technicianOnDuty = $data['technicianOnDuty'] ?? null;
$soundEngineer = $data['soundEngineer'] ?? null;
$lightOperator = $data['lightOperator'] ?? null;

$totalPrice = $data['totalPrice']['price'] ?? $data['totalPrice'] ?? 0;

$utmTags = $data['utmTags'] ?? [];

$message = "📋 *Новая заявка*\n\n";
$message .= "👤 *Имя:* {$name}\n";
$message .= "📞 *Телефон:* {$phone}\n";
$message .= "📅 *Дата:* {$date}\n";
$message .= "💬 *Комментарий:* " . ($comments ?: '—') . "\n\n";

$message .= "━━━━━━━━━━━━━━━\n\n";

$message .= "📦 *Тариф:* {$tariff}\n";
$message .= "⏱ *Часов аренды:* {$rentTime['hours']}\n";
$message .= "🔧 *Технических часов:* {$techTime['hours']}\n";

$message .= "👨‍🔧 *Дежурный техник:* " . ($technicianOnDuty ? formatPrice($technicianOnDuty) : '—') . "\n";
$message .= "🎤 *Звукорежиссёр:* " . ($soundEngineer ? formatPrice($soundEngineer) : '—') . "\n";
$message .= "💡 *Светорежиссёр:* " . ($lightOperator ? formatPrice($lightOperator) : '—') . "\n\n";

$message .= "━━━━━━━━━━━━━━━\n\n";

$message .= "💰 *Итоговая сумма:* " . formatPrice($totalPrice) . "\n";

if (!empty($utmTags)) {
    $message .= "\n━━━━━━━━━━━━━━━\n\n";
    $message .= "📊 *UTM tags:*\n";
    foreach ($utmTags as $tag) {
        $tagName = htmlspecialchars($tag['name'] ?? '');
        $tagValue = htmlspecialchars($tag['value'] ?? '');
        $message .= "• {$tagName}: {$tagValue}\n";
    }
}

$success = true;
foreach ($chatIds as $chatId) {
    $result = sendTelegramMessage($botToken, $chatId, $message);
    if (!$result) {
        $success = false;
    }
}

if ($success) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send message']);
}

function sendTelegramMessage($botToken, $chatId, $message) {
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    
    $postData = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'Markdown',
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
}

function formatPrice($price) {
    return number_format($price, 0, '', ' ') . ' ₽';
}
