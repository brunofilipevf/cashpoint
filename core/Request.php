<?php

namespace Core;

class Request
{
    public function method()
    {
        if (!isset($_SERVER['REQUEST_METHOD'])) {
            return 'GET';
        }

        return $_SERVER['REQUEST_METHOD'];
    }

    public function uri()
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            return '/';
        }

        $uri = $_SERVER['REQUEST_URI'];
        $uri = parse_url($uri, PHP_URL_PATH);

        if (!$uri || str_contains($uri, '//') || str_contains($uri, '..') || str_contains($uri, "\0")) {
            return '/';
        }

        return $uri;
    }

    public function input($key)
    {
        if (!isset($_POST[$key])) {
            return null;
        }

        $value = $_POST[$key];

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return $value;
    }
}
