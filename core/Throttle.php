<?php

namespace Core;

class Throttle
{
    public function __construct(
        private Request $request,
        private Response $response
    ) {}

    public function handle($maxRequests = 40, $windowSeconds = 60)
    {
        // -------------------------------------------------------------------
        // Prepara arquivo, conta requisições do IP e bloqueia se exceder
        // -------------------------------------------------------------------

        $file = __DIR__ . '/../storage/throttle.tmp';
        $ip = $this->request->ip();
        $now = time();
        $windowStart = $now - $windowSeconds;
        $requestCount = $this->countAndClean($file, $ip, $windowStart);

        // -------------------------------------------------------------------
        // Bloqueia se atingiu o limite
        // -------------------------------------------------------------------

        if ($requestCount >= $maxRequests) {
            $this->response->abort(429);
        }

        // -------------------------------------------------------------------
        // Registra a requisição atual
        // -------------------------------------------------------------------

        file_put_contents($file, "{$ip},{$now}\n", FILE_APPEND | LOCK_EX);
    }

    private function countAndClean($file, $ip, $windowStart)
    {
        // -------------------------------------------------------------------
        // Cria arquivo se não existir
        // -------------------------------------------------------------------

        if (!file_exists($file)) {
            touch($file);
            return 0;
        }

        // -------------------------------------------------------------------
        // Reseta arquivo se exceder 10MB
        // -------------------------------------------------------------------

        if (filesize($file) > 10485760) {
            file_put_contents($file, '');
            return 0;
        }

        // -------------------------------------------------------------------
        // Lê todas as linhas do arquivo
        // -------------------------------------------------------------------

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!$lines) {
            return 0;
        }

        // -------------------------------------------------------------------
        // Conta requisições do IP e filtra linhas válidas
        // -------------------------------------------------------------------

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

        // -------------------------------------------------------------------
        // Limpeza probabilística (10% de chance)
        // -------------------------------------------------------------------

        if ($needsCleanup && mt_rand(1, 10) === 1) {
            file_put_contents($file, implode("\n", $validLines) . "\n", LOCK_EX);
        }

        return $count;
    }
}
