<?php

namespace Core;

class Request
{
    public static function method()
    {
        if (!isset($_SERVER['REQUEST_METHOD'])) {
            return 'GET';
        }

        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    public static function uri()
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            return '/';
        }

        $uri = $_SERVER['REQUEST_URI'];
        $uri = parse_url($uri, PHP_URL_PATH);

        if (!$uri) {
            return '/';
        }

        $uri = urldecode($uri);

        if (!preg_match('#^[a-zA-Z0-9/\.]+$#', $uri)) {
            return '/';
        }

        return $uri;
    }

    public static function ip()
    {
        if (!isset($_SERVER['REMOTE_ADDR'])) {
            return '0.0.0.0';
        }

        $ip = $_SERVER['REMOTE_ADDR'];

        if ($ip === '127.0.0.1' || $ip === '::1') {
            return '127.0.0.1';
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return '0.0.0.0';
        }

        return $ip;
    }

    public static function input($key)
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

    public static function json()
    {
        $input = file_get_contents('php://input');

        if (!$input) {
            return [];
        }

        $data = json_decode($input, true);

        if (!is_array($data)) {
            return [];
        }

        $normalized = [];

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                continue;
            }

            $value = trim($value);

            if ($value === '') {
                $value = null;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
