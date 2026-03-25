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

$emailEnabled = $config['email_enabled'] ?? false;
$emailTo = $config['email_to'] ?? '';
$emailFrom = $config['email_from'] ?? '';
$emailFromName = $config['email_from_name'] ?? '';

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

$telegramSuccess = true;
foreach ($chatIds as $chatId) {
    $result = sendTelegramMessage($botToken, $chatId, $message);
    if (!$result) {
        $telegramSuccess = false;
    }
}

$emailSuccess = true;
if ($emailEnabled && !empty($emailTo)) {
    $emailSuccess = sendEmail(
        $emailTo,
        $emailFrom,
        $emailFromName,
        $name,
        $phone,
        $date,
        $comments,
        $tariff,
        $rentTime,
        $techTime,
        $technicianOnDuty,
        $soundEngineer,
        $lightOperator,
        $totalPrice,
        $utmTags
    );
}

if ($telegramSuccess && $emailSuccess) {
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

function sendEmail($to, $from, $fromName, $name, $phone, $date, $comments, $tariff, $rentTime, $techTime, $technicianOnDuty, $soundEngineer, $lightOperator, $totalPrice, $utmTags) {
    $subject = "=?UTF-8?B?" . base64_encode("Новая заявка от {$name}") . "?=";
    
    $utmHtml = '';
    if (!empty($utmTags)) {
        $utmHtml = '<tr><td colspan="2" style="padding: 15px 0 10px; border-top: 1px solid #eee;"><strong>UTM tags:</strong></td></tr>';
        foreach ($utmTags as $tag) {
            $tagName = htmlspecialchars($tag['name'] ?? '');
            $tagValue = htmlspecialchars($tag['value'] ?? '');
            $utmHtml .= "<tr><td style='padding: 5px 0; color: #666;'>{$tagName}</td><td style='padding: 5px 0;'>{$tagValue}</td></tr>";
        }
    }
    
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;'>
        <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; text-align: center;'>
            <h1 style='color: white; margin: 0;'>Новая заявка</h1>
        </div>
        
        <div style='padding: 20px; background: #f9f9f9;'>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee; width: 40%;'><strong>Имя</strong></td>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'>{$name}</td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'><strong>Телефон</strong></td>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'><a href='tel:{$phone}' style='color: #667eea;'>{$phone}</a></td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'><strong>Дата</strong></td>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'>{$date}</td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'><strong>Комментарий</strong></td>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'>" . ($comments ?: '—') . "</td>
                </tr>
            </table>
        </div>
        
        <div style='padding: 20px; background: white;'>
            <h2 style='color: #667eea; margin-top: 0;'>Детали заказа</h2>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'><strong>Тариф</strong></td>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'>{$tariff}</td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'><strong>Часов аренды</strong></td>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'>{$rentTime['hours']}</td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'><strong>Технических часов</strong></td>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'>{$techTime['hours']}</td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'><strong>Дежурный техник</strong></td>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'>" . ($technicianOnDuty ? formatPrice($technicianOnDuty) : '—') . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'><strong>Звукорежиссёр</strong></td>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'>" . ($soundEngineer ? formatPrice($soundEngineer) : '—') . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'><strong>Светорежиссёр</strong></td>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'>" . ($lightOperator ? formatPrice($lightOperator) : '—') . "</td>
                </tr>
            </table>
        </div>
        
        <div style='padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); text-align: center;'>
            <p style='color: white; font-size: 24px; margin: 0;'><strong>Итого: " . formatPrice($totalPrice) . "</strong></p>
        </div>
        
        " . (!empty($utmTags) ? "
        <div style='padding: 20px; background: #f5f5f5; font-size: 12px; color: #666;'>
            <table style='width: 100%; border-collapse: collapse;'>
                {$utmHtml}
            </table>
        </div>
        " : "") . "
    </body>
    </html>
    ";
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$from}>",
        "Reply-To: {$from}",
    ];
    
    return mail($to, $subject, $html, implode("\r\n", $headers));
}
