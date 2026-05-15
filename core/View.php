<?php

namespace Core;

use DateTime;
use RuntimeException;
use Throwable;

class View
{
    private $data = [];
    private $vars = [];

    public function __construct(
        private Container $container
    ) { }

    public function render($path, $data = [])
    {
        $this->data = $data;
        return $this->include($path);
    }

    private function helpers()
    {
        return [
            'include' => [$this, 'include'],
            'set' => [$this, 'set'],
            'get' => [$this, 'get'],
            'isRoute' => [$this, 'isRoute'],
            'e' => [$this, 'escape']
        ];
    }

    private function include($path)
    {
        $viewPath = dirname(__DIR__) . '/app/views/';
        $viewFile = $path . '.php';

        if (!is_file($viewPath . $viewFile)) {
            throw new RuntimeException("View {$viewFile} não encontrada");
        }

        extract($this->data, EXTR_SKIP);
        extract($this->helpers(), EXTR_SKIP);
        ob_start();

        try {
            include $viewPath . $viewFile;
            return ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            throw new RuntimeException("Erro ao renderizar view {$viewFile}: " . $e->getMessage(), 0, $e);
        }
    }

    private function set($key, $value)
    {
        $this->vars[$key] = $value;
    }

    private function get($key)
    {
        if (isset($this->vars[$key])) {
            return $this->vars[$key];
        }

        return match ($key) {
            'app_name' => APP_NAME,
            'app_author' => APP_AUTHOR,
            'app_description' => APP_DESCRIPTION,
            'flash_message' => $this->session()->getFlash(),
            'csrf_token' => $this->session()->getCsrf(),
            default => throw new RuntimeException("Variável '{$key}' não encontrada na View")
        };
    }

    private function isRoute($route, $strict = false)
    {
        $currentUri = $this->request()->uri();

        if ($strict) {
            return $currentUri === $route;
        }

        if ($route === '/') {
            return $currentUri === '/';
        }

        return str_starts_with($currentUri, $route);
    }

    private function escape($value, $format = null, $dash = false)
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
                'currency' => $this->formatCurrency($value),
                'date' => $this->formatDate($value, $param),
                'document' => $this->formatDocument($value),
                'status' => $this->formatStatus($value),
                default => throw new RuntimeException("Formato '{$format}' não encontrado na View")
            };
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function formatCurrency($value)
    {
        $numeric = (float) $value;
        return 'R$ ' . number_format($numeric, 2, ',', '.');
    }

    private function formatDate($value, $param = null)
    {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $value);

        if (!$date) {
            return $value;
        }

        if ($param === null || $param === '') {
            return $date->format('d/m/Y H:i:s');
        }

        try {
            return $date->format($param);
        } catch (Throwable $e) {
            return $value;
        }
    }

    private function formatDocument($value)
    {
        $clean = preg_replace('/\D/', '', $value);
        $length = mb_strlen($clean, 'UTF-8');

        return match ($length) {
            11 => preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $clean),
            14 => preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $clean),
            default => $value
        };
    }

    private function formatStatus($value)
    {
        if ((int) $value !== 1) {
            return 'Inativo';
        }

        return 'Ativo';
    }

    private function request()
    {
        return $this->container->get(Request::class);
    }

    private function session()
    {
        return $this->container->get(Session::class);
    }
}
