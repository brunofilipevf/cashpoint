<?php

namespace Core;

class Throttle
{
    public static function handle($maxRequests = 40, $windowSeconds = 60)
    {
        $file = __DIR__ . '/../storage/throttle.tmp';
        $ip = Request::ip();
        $now = time();
        $windowStart = $now - $windowSeconds;
        $requestCount = self::countAndClean($file, $ip, $windowStart);

        if ($requestCount >= $maxRequests) {
            Response::abort(429);
        }

        self::appendRequest($file, $ip, $now);
    }

    private static function countAndClean($file, $ip, $windowStart)
    {
        if (!file_exists($file)) {
            touch($file);
            return 0;
        }

        if (filesize($file) > 10485760) {
            file_put_contents($file, '');
            return 0;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!$lines) {
            return 0;
        }

        $count = 0;
        $validLines = [];
        $needsCleanup = false;

        foreach ($lines as $line) {
            $parts = explode(',', $line, 2);

            if (count($parts) !== 2) {
                $needsCleanup = true;
                continue;
            }

            $time = (int)$parts[1];

            if ($time >= $windowStart) {
                $validLines[] = $line;

                if ($parts[0] === $ip) {
                    $count++;
                }
            } else {
                $needsCleanup = true;
            }
        }

        if ($needsCleanup && mt_rand(1, 10) === 1) {
            file_put_contents($file, implode("\n", $validLines) . "\n", LOCK_EX);
        }

        return $count;
    }

    private static function appendRequest($file, $ip, $time)
    {
        file_put_contents($file, "{$ip},{$time}\n", FILE_APPEND | LOCK_EX);
    }
}
