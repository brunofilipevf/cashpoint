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

    public function userIp()
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

    public function userAgent()
    {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return 'unknown_user_agent';
        }

        $agent = $_SERVER['HTTP_USER_AGENT'];
        $agent = htmlspecialchars($agent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $agent = trim($agent);

        if ($agent === '') {
            return 'unknown_user_agent';
        }

        return $agent;
    }
}
