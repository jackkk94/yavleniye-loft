<?php

header('Content-Type: application/json');

const RATE_LIMIT_MAX_REQUESTS = 5;
const RATE_LIMIT_WINDOW_SECONDS = 600;
const MAX_UTM_TAGS = 20;

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

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Honeypot: поле скрыто от людей, заполняют его только боты
if (!empty($data['extraInfo'])) {
    echo json_encode(['success' => true]);
    exit;
}

if (isRateLimited($_SERVER['REMOTE_ADDR'] ?? 'unknown', RATE_LIMIT_MAX_REQUESTS, RATE_LIMIT_WINDOW_SECONDS)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests']);
    exit;
}

$name = escapeText($data['name'] ?? '', 100);
$phone = escapeText($data['phone'] ?? '', 30);
$date = escapeText($data['date'] ?? '', 20);
$comments = escapeText($data['comments'] ?? '', 1000);

$isTariffWithEquipment = $data['isTariffWithEquipment'] ?? false;
$tariff = $isTariffWithEquipment ? 'С оборудованием' : 'Без оборудования';

$rentTime = ['hours' => toNumber($data['rentTime']['hours'] ?? 0)];
$techTime = ['hours' => toNumber($data['techTime']['hours'] ?? 0)];

$technicianOnDuty = toNumber($data['technicianOnDuty'] ?? 0);
$soundEngineer = toNumber($data['soundEngineer'] ?? 0);
$lightOperator = toNumber($data['lightOperator'] ?? 0);

$totalPrice = toNumber($data['totalPrice']['price'] ?? $data['totalPrice'] ?? 0);

$utmTags = [];
$rawUtmTags = $data['utmTags'] ?? [];
foreach (array_slice(is_array($rawUtmTags) ? $rawUtmTags : [], 0, MAX_UTM_TAGS) as $tag) {
    if (!is_array($tag)) {
        continue;
    }
    $tagName = escapeText($tag['name'] ?? '', 50);
    if ($tagName === '') {
        continue;
    }
    $utmTags[] = [
        'name' => $tagName,
        'value' => escapeText($tag['value'] ?? '', 200),
    ];
}

$sentAt = (new DateTime('now', new DateTimeZone('Europe/Moscow')))->format('d.m.Y H:i:s') . ' (МСК)';
$acceptPolicy = ($data['acceptPolicy'] ?? false) === true ? 'Да' : 'Нет';
$enableMailing = ($data['enableMailing'] ?? false) === true ? 'Да' : 'Нет';

$message = "📋 <b>Новая заявка</b>\n\n";
$message .= "👤 <b>Имя:</b> {$name}\n";
$message .= "📞 <b>Телефон:</b> {$phone}\n";
$message .= "📅 <b>Дата:</b> {$date}\n";
$message .= "💬 <b>Комментарий:</b> " . ($comments ?: '—') . "\n\n";

$message .= "━━━━━━━━━━━━━━━\n\n";

$message .= "📦 <b>Тариф:</b> {$tariff}\n";
$message .= "⏱ <b>Часов аренды:</b> {$rentTime['hours']}\n";
$message .= "🔧 <b>Технических часов:</b> {$techTime['hours']}\n";

$message .= "👨‍🔧 <b>Дежурный техник:</b> " . ($technicianOnDuty ? formatPrice($technicianOnDuty) : '—') . "\n";
$message .= "🎤 <b>Звукорежиссёр:</b> " . ($soundEngineer ? formatPrice($soundEngineer) : '—') . "\n";
$message .= "💡 <b>Светорежиссёр:</b> " . ($lightOperator ? formatPrice($lightOperator) : '—') . "\n\n";

$message .= "━━━━━━━━━━━━━━━\n\n";

$message .= "💰 <b>Итоговая сумма:</b> " . formatPrice($totalPrice) . "\n";

if (!empty($utmTags)) {
    $message .= "\n━━━━━━━━━━━━━━━\n\n";
    $message .= "📊 <b>UTM tags:</b>\n";
    foreach ($utmTags as $tag) {
        $message .= "• {$tag['name']}: {$tag['value']}\n";
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
        $utmTags,
        $sentAt,
        $acceptPolicy,
        $enableMailing
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
        'parse_mode' => 'HTML',
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

function escapeText($value, $maxLength) {
    if (!is_scalar($value)) {
        return '';
    }
    $value = trim((string) $value);
    $value = function_exists('mb_substr') ? mb_substr($value, 0, $maxLength, 'UTF-8') : substr($value, 0, $maxLength);

    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function toNumber($value) {
    return is_numeric($value) ? max(0, (float) $value) : 0;
}

function isRateLimited($ip, $maxRequests, $windowSeconds) {
    $dir = sys_get_temp_dir() . '/yavleniye-loft-rate-limit';
    if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
        return false;
    }

    $fp = @fopen($dir . '/' . hash('sha256', $ip), 'c+');
    if (!$fp) {
        return false;
    }

    flock($fp, LOCK_EX);
    $now = time();
    $timestamps = [];
    foreach (explode("\n", (string) stream_get_contents($fp)) as $line) {
        $timestamp = (int) $line;
        if ($timestamp > $now - $windowSeconds) {
            $timestamps[] = $timestamp;
        }
    }

    $limited = count($timestamps) >= $maxRequests;
    if (!$limited) {
        $timestamps[] = $now;
    }

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, implode("\n", $timestamps));
    flock($fp, LOCK_UN);
    fclose($fp);

    return $limited;
}

function sendEmail($to, $from, $fromName, $name, $phone, $date, $comments, $tariff, $rentTime, $techTime, $technicianOnDuty, $soundEngineer, $lightOperator, $totalPrice, $utmTags, $sentAt, $acceptPolicy, $enableMailing) {
    $subject = "=?UTF-8?B?" . base64_encode("Новая заявка от {$name}") . "?=";
    
    $utmHtml = '';
    if (!empty($utmTags)) {
        $utmHtml = '<tr><td colspan="2" style="padding: 15px 0 10px; border-top: 1px solid #eee;"><strong>UTM tags:</strong></td></tr>';
        foreach ($utmTags as $tag) {
            $utmHtml .= "<tr><td style='padding: 5px 0; color: #666;'>{$tag['name']}</td><td style='padding: 5px 0;'>{$tag['value']}</td></tr>";
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
        
        <div style='padding: 20px; background: white;'>
            <h2 style='color: #667eea; margin-top: 0;'>Согласия</h2>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee; width: 60%;'><strong>Дата и время отправки</strong></td>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'>{$sentAt}</td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'><strong>Согласие на обработку персональных данных</strong></td>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'>{$acceptPolicy}</td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'><strong>Согласие на получение рассылки</strong></td>
                    <td style='padding: 10px 0; border-bottom: 1px solid #eee;'>{$enableMailing}</td>
                </tr>
            </table>
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
