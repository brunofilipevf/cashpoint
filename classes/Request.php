<?php

class Request
{
    public function get($key, $default = null)
    {
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }
        return $default;
    }

    public function post($key, $default = null)
    {
        if (isset($_POST[$key])) {
            return $_POST[$key];
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