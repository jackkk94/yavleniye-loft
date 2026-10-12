<?php

require __DIR__ . '/php/helpers.php';
require __DIR__ . '/php/rate-limit.php';
require __DIR__ . '/php/telegram.php';
require __DIR__ . '/php/email.php';

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

$message = buildTelegramMessage(
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
