<?php

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
