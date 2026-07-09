<?php

namespace Core;

class View
{
    private static $data = [];
    private static $vars = [];

    public static function render($path, $data = [])
    {
        self::$data = $data;
        return self::loadTemplate($path);
    }

    private static function helpers()
    {
        return [
            'partial' => [self::class, 'getPartial'],
            'set' => [self::class, 'setVar'],
            'get' => [self::class, 'getVar'],
            'flash' => [self::class, 'getFlash'],
            'csrf' => [self::class, 'getCsrf'],
            'isRoute' => [self::class, 'isRoute'],
            'e' => [self::class, 'escape']
        ];
    }

    private static function loadTemplate($path)
    {
        $fullPath = __DIR__ . "/../views/{$path}.php";

        if (!is_file($fullPath)) {
            throw new \RuntimeException("[View] Template '{$path}' não encontrado");
        }

        extract(self::helpers(), EXTR_SKIP);
        extract(self::$data, EXTR_SKIP);

        ob_start();

        try {
            include $fullPath;
            return ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    private static function getPartial($path)
    {
        echo self::loadTemplate($path);
    }

    private static function setVar($key, $value)
    {
        if (is_string($value)) {
            self::$vars[$key] = $value;
        }
    }

    private static function getVar($key)
    {
        if (isset(self::$vars[$key])) {
            return self::escape(self::$vars[$key]);
        }

        return match ($key) {
            'app_name' => self::escape(APP_NAME),
            'app_author' => self::escape(APP_AUTHOR),
            'app_description' => self::escape(APP_DESCRIPTION),
            default => null
        };
    }

    private static function getCsrf()
    {
        $token = Session::getCsrf();
        return self::escape($token);
    }

    private static function getFlash()
    {
        $flash = Session::getFlash();

        if (isset($flash['type'], $flash['message'])) {
            $flash['type'] = self::escape($flash['type']);
            $flash['message'] = nl2br(self::escape($flash['message']));
            return $flash;
        }

        return [];
    }

    private static function isRoute($route, $strict = false)
    {
        $uri = Request::uri();

        if ($strict) {
            return $uri === $route;
        }

        if ($route === '/') {
            return $uri === '/';
        }

        return str_starts_with($uri, $route);
    }

    private static function escape($value, $format = null)
    {
        if (!is_scalar($value)) {
            if ($format === 'dash') {
                return '—';
            }
            return '';
        }

        $value = trim((string) $value);

        if ($value === '') {
            if ($format === 'dash') {
                return '—';
            }
            return '';
        }

        if ($format !== null && $format !== 'dash') {
            $parts = explode(':', $format, 2);
            $name = $parts[0];

            if (isset($parts[1])) {
                $param = $parts[1];
            } else {
                $param = null;
            }

            $value = match ($name) {
                'currency' => self::formatCurrency($value),
                'date' => self::formatDate($value, $param),
                'document' => self::formatDocument($value, $param),
                default => throw new \RuntimeException("[View] Formato não encontrado para '{$name}'")
            };
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private static function formatCurrency($value)
    {
        if (is_numeric($value)) {
            return 'R$ ' . number_format($value, 2, ',', '.');
        }

        return $value;
    }

    private static function formatDate($value, $format = null)
    {
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);

        if (!$date) {
            return $value;
        }

        return match ($format) {
            null => $date->format('d-m-Y \à\s H:i:s'),
            'd-m-Y' => $date->format('d-m-Y'),
            'Y-m-d' => $date->format('Y-m-d'),
            default => $value
        };
    }

    private static function formatDocument($value, $format = null)
    {
        $clean = preg_replace('/\D/', '', $value);
        $count = mb_strlen($clean, 'UTF-8');

        if ($format === 'hidden' && $count === 11) {
            return substr($clean, 0, 3) . '.***.***-' . substr($clean, -2);
        }

        return match ($count) {
            11 => preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $clean),
            14 => preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $clean),
            default => $value
        };
    }
}
