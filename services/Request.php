<?php

namespace Services;

class Request
{
    private static $instance;

    public static function getInstance()
    {
        return self::$instance ?? self::$instance = new self();
    }

    public function getUri()
    {
        return filter_input(INPUT_SERVER, 'REQUEST_URI') ?? '/';
    }

    public function getMethod()
    {
        return filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    }

    public function userIp()
    {
        $headers = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];

        foreach ($headers as $header) {
            $ip = filter_input(INPUT_SERVER, $header);

            if (!is_string($ip)) {
                continue;
            }

            $ip = trim(explode(',', $ip, 2)[0]);

            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return '0.0.0.0';
    }

    public function userAgent()
    {
        $agent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');

        if ($agent === null) {
            return 'Unknown';
        }

        return mb_substr($agent, 0, 255);
    }

    public function query($key, $default = null)
    {
        $value = filter_input(INPUT_GET, $key);

        if ($value === null) {
            return $default;
        }

        return $this->filter($value);
    }

    public function input($key, $default = null)
    {
        $value = filter_input(INPUT_POST, $key);

        if ($value === null) {
            return $default;
        }

        return $this->filter($value);
    }

    private function filter($value)
    {
        if (is_array($value)) {
            return array_map(fn($v) => $this->filter($v), $value);
        }

        if (is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            if (!mb_check_encoding($value, 'UTF-8')) {
                return null;
            }

            $value = trim($value);

            return $value === '' ? null : $value;
        }

        return null;
    }
}
