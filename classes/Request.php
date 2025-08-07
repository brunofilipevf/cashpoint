<?php

class Request
{
    public function get($key, $default = null)
    {
        if (isset($_GET[$key])) {
            $value = $_GET[$key];
            return is_string($value) ? trim($value) : $value;
        }
        return $default;
    }

    public function post($key, $default = null)
    {
        if (isset($_POST[$key])) {
            $value = $_POST[$key];
            return is_string($value) ? trim($value) : $value;
        }
        return $default;
    }

    public function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    public function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}