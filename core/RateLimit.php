<?php

namespace Core;

class RateLimit
{
    public function __construct(
        private Request $request,
        private Response $response
    ) {}

    public function handle($maxRequests = 40, $windowSeconds = 60)
    {
        $file = ABS_PATH . '/storage/rate_limit.tmp';
        $ip = $this->request->ip();
        $now = time();
        $windowStart = $now - $windowSeconds;

        $handle = fopen($file, 'c+');

        if (!$handle) {
            return;
        }

        flock($handle, LOCK_EX);

        $requestCount = $this->countAndClean($handle, $ip, $windowStart);

        if ($requestCount >= $maxRequests) {
            flock($handle, LOCK_UN);
            fclose($handle);

            $this->response->abort(429);
        }

        fseek($handle, 0, SEEK_END);
        fwrite($handle, "{$ip},{$now}\n");

        flock($handle, LOCK_UN);
        fclose($handle);
    }

    private function countAndClean($handle, $ip, $windowStart)
    {
        rewind($handle);

        $lines = [];

        while (($line = fgets($handle)) !== false) {
            $lines[] = trim($line);
        }

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

            $time = (int) $parts[1];

            if ($time >= $windowStart) {
                $validLines[] = $line;

                if ($parts[0] === $ip) {
                    $count++;
                }
            } else {
                $needsCleanup = true;
            }
        }

        if ($needsCleanup && random_int(1, 10) === 1) {
            rewind($handle);
            ftruncate($handle, 0);
            fwrite($handle, implode("\n", $validLines) . "\n");
        }

        return $count;
    }
}
