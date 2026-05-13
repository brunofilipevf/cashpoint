<?php

namespace Core;

use DateTime;
use RuntimeException;
use Throwable;

class View
{
    private static $data = [];
    private static $vars = [];

    public static function render($path, $data = [])
    {
        self::$data = $data;
        return self::include($path);
    }

    private static function helpers()
    {
        return [
            'include' => [self::class, 'include'],
            'set' => [self::class, 'set'],
            'get' => [self::class, 'get'],
            'isBaseRoute' => [self::class, 'isBaseRoute'],
            'e' => [self::class, 'escape']
        ];
    }

    private static function include($path)
    {
        $viewPath = dirname(__DIR__) . '/app/views/';
        $viewFile = $path . '.php';

        if (!is_file($viewPath . $viewFile)) {
            throw new RuntimeException("View {$viewFile} não encontrada");
        }

        extract(self::$data, EXTR_SKIP);
        extract(self::helpers(), EXTR_SKIP);
        ob_start();

        try {
            include $viewPath . $viewFile;
            return ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            throw new RuntimeException("Erro ao renderizar view {$viewFile}: " . $e->getMessage(), 0, $e);
        }
    }

    private static function set($key, $value)
    {
        self::$vars[$key] = $value;
    }

    private static function get($key)
    {
        if (isset(self::$vars[$key])) {
            return self::$vars[$key];
        }

        return match ($key) {
            'app_name' => APP_NAME,
            'app_author' => APP_AUTHOR,
            'app_description' => APP_DESCRIPTION,
            'flash_message' => Session::getFlash(),
            'csrf_token' => Session::getCsrf(),
            default => throw new RuntimeException("Variável '{$key}' não encontrada na View")
        };
    }

    private static function isBaseRoute($baseRoute)
    {
        $currentUri = Request::uri();

        if ($baseRoute === '/') {
            return $currentUri === '/';
        }

        return str_starts_with($currentUri, $baseRoute);
    }

    private static function escape($value, $format = null, $dash = false)
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            if ($dash) {
                return '—';
            }
            return '';
        }

        $value = (string) trim($value);

        if ($value === '') {
            if ($dash) {
                return '—';
            }
            return '';
        }

        if ($format !== null) {
            [$name, $param] = explode(':', $format, 2) + [null, null];
            $value = match ($name) {
                'currency' => self::formatCurrency($value),
                'date' => self::formatDate($value, $param),
                'document' => self::formatDocument($value),
                'status' => self::formatStatus($value),
                default => throw new RuntimeException("Formato '{$format}' não encontrado na View")
            };
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private static function formatCurrency($value)
    {
        $numeric = (float) $value;
        return 'R$ ' . number_format($numeric, 2, ',', '.');
    }

    private static function formatDate($value, $param = null)
    {
        if ($param !== null) {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $value);

            if ($date) {
                return $date->format($param);
            }

            return $value;
        }

        $date = DateTime::createFromFormat('Y-m-d H:i:s', $value);

        if ($date) {
            return $date->format('d/m/Y H:i:s');
        }

        return $value;
    }

    private static function formatDocument($value)
    {
        $clean = preg_replace('/\D/', '', $value);
        $length = mb_strlen($clean, 'UTF-8');

        return match ($length) {
            11 => preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $clean),
            14 => preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $clean),
            default => $value
        };
    }

    private static function formatStatus($value)
    {
        if ((int) $value !== 1) {
            return 'Inativo';
        }

        return 'Ativo';
    }
}
