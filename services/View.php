<?php

namespace Services;

class View
{
    private $blocks = [];
    private $data = [];

    public function render($path, $data = [])
    {
        $this->data['appName'] = APP_NAME;
        $this->data['appDescription'] = APP_DESCRIPTION;
        $this->data['currentYear'] = date('Y');

        $this->data = $this->data + $data;

        extract($this->data);
        ob_start();
        require(ABSPATH . '/views/' . ltrim($path, '/') . '.php');
        return ob_get_clean();
    }

    private function partial($path)
    {
        extract($this->data);
        ob_start();
        require(ABSPATH . '/views/' . ltrim($path, '/') . '.php');
        echo ob_get_clean();
    }

    private function block($name, $content)
    {
        $this->blocks[$name] = is_string($content) ? $content : null;
    }

    private function yield($name)
    {
        return $this->blocks[$name] ?? null;
    }

    private function url($path, $id = null)
    {
        $path = '/' . ltrim($path, '/');

        if ($id !== null) {
            $path .= '/' . $this->e($id);
        }

        return APP_URL . $path;
    }

    private function e($value, $filter = null)
    {
        if ($value === null) {
            return $filter === 'dash' ? '—' : null;
        }

        $value = htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if ($filter === 'uppercase') {
            return mb_strtoupper($value);
        }

        if ($filter === 'lowercase') {
            return mb_strtolower($value);
        }

        if ($filter === 'nl2br') {
            return nl2br($value);
        }

        if ($filter === 'bool') {
            return $value == 1 ? 'Sim' : 'Não';
        }

        if ($filter === 'datetime') {
            return date('d/m/Y \à\s H:i:s', strtotime($value));
        }

        return $value;
    }
}
