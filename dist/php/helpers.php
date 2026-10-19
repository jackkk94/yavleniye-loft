<?php

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
