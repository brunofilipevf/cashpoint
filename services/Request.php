<?php

namespace Services;

class Request
{
    public function get($key, $default = null)
    {
        $value = $_GET[$key] ?? $default;

        if (is_array($value)) {
            return array_map(fn($item) => $this->normalizeValue($item), $value);
        }

        return $this->normalizeValue($value);
    }

    public function input($key, $default = null)
    {
        $value = $_POST[$key] ?? $default;

        if (is_array($value)) {
            return array_map(fn($item) => $this->normalizeValue($item), $value);
        }

        return $this->normalizeValue($value);
    }

    private function normalizeValue($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return $value;
    }
}
