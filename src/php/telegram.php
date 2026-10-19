<?php

function buildTelegramMessage($name, $phone, $date, $comments, $tariff, $rentTime, $techTime, $technicianOnDuty, $soundEngineer, $lightOperator, $totalPrice, $utmTags) {
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

    return $message;
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
