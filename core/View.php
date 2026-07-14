<?php

namespace Core;

class View
{
    private $data = [];
    private $vars = [];

    public function __construct(
        private Request $request,
        private Session $session
    ) {}

    public function render($path, $data = [])
    {
        $this->data = $data;
        return $this->loadTemplate($path);
    }

    private function helpers()
    {
        return [
            'partial' => [$this, 'getPartial'],
            'set' => [$this, 'setVar'],
            'get' => [$this, 'getVar'],
            'flash' => [$this, 'getFlash'],
            'csrf' => [$this, 'getCsrf'],
            'isRoute' => [$this, 'isRoute'],
            'e' => [$this, 'escape']
        ];
    }

    private function loadTemplate($path)
    {
        $fullPath = __DIR__ . "/../views/{$path}.php";

        if (!is_file($fullPath)) {
            throw new \RuntimeException("[View] Template '{$path}' não encontrado");
        }

        extract($this->helpers(), EXTR_SKIP);
        extract($this->data, EXTR_SKIP);

        ob_start();

        try {
            include $fullPath;
            return ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    private function getPartial($path)
    {
        echo $this->loadTemplate($path);
    }

    private function setVar($key, $value)
    {
        if (is_string($value)) {
            $this->vars[$key] = $value;
        }
    }

    private function getVar($key)
    {
        if (isset($this->vars[$key])) {
            return $this->escape($this->vars[$key]);
        }

        return match ($key) {
            'app_name' => $this->escape(APP_NAME),
            'app_author' => $this->escape(APP_AUTHOR),
            'app_description' => $this->escape(APP_DESCRIPTION),
            default => null
        };
    }

    private function getCsrf()
    {
        $token = $this->session->getCsrf();
        return $this->escape($token);
    }

    private function getFlash()
    {
        $flash = $this->session->getFlash();

        if (isset($flash['type'], $flash['message'])) {
            $flash['type'] = $this->escape($flash['type']);
            $flash['message'] = nl2br($this->escape($flash['message']));

            return $flash;
        }

        return [];
    }

    private function isRoute($route, $strict = false)
    {
        $uri = $this->request->uri();

        if ($strict) {
            return $uri === $route;
        }

        if ($route === '/') {
            return $uri === '/';
        }

        return str_starts_with($uri, $route);
    }

    private function escape($value, $format = null)
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
                'currency' => $this->formatCurrency($value),
                'date' => $this->formatDate($value, $param),
                'document' => $this->formatDocument($value, $param),
                default => throw new \RuntimeException("[View] Formato não encontrado para '{$name}'")
            };
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function formatCurrency($value)
    {
        if (is_numeric($value)) {
            return 'R$ ' . number_format($value, 2, ',', '.');
        }

        return $value;
    }

    private function formatDate($value, $format = null)
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

    private function formatDocument($value, $format = null)
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
