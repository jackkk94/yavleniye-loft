<?php

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
