<?php

namespace Core;

class Environment
{
    private static $cache = [];

    public static function get($key)
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        return self::resolve($key);
    }

    private static function resolve($key)
    {
        $file = __DIR__ . '/../.env';

        if (!file_exists($file)) {
            throw new \RuntimeException('[Environment] Arquivo .env não encontrado');
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            $parts = explode('=', $line, 2);
            $currentKey = trim($parts[0]);
            $value = trim($parts[1]);
            $value = trim($value, '"\'');
            $value = str_replace('\n', PHP_EOL, $value);

            self::$cache[$currentKey] = $value;
        }

        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        throw new \RuntimeException("[Environment] Chave de ambiente '{$key}' não encontrada");
    }
}
