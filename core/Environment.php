<?php

namespace Core;

class Environment
{
    public static function get($key)
    {
        $file = __DIR__ . '/../.environment';

        if (!file_exists($file)) {
            throw new \RuntimeException('[Environment] Arquivo .environment não encontrado');
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                $parts = explode('=', $line, 2);
                $currentKey = trim($parts[0]);

                if ($currentKey === $key) {
                    $value = trim($parts[1]);
                    $value = trim($value, '"\'');
                    $value = str_replace('\n', PHP_EOL, $value);
                    return $value;
                }
            }
        }

        throw new \RuntimeException("[Environment] Chave de ambiente '{$key}' não encontrada");
    }
}
