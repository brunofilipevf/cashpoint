<?php

namespace Services;

class Response
{
    private static $instance;

    public static function getInstance()
    {
        return self::$instance ?? self::$instance = new self();
    }

    public function send($content, $statusCode = 200)
    {
        $content = (string) $content;

        http_response_code($statusCode);
        header('Content-Type: text/html; charset=utf-8', true);
        header('Content-Length: ' . strlen($content), true);

        echo $content;
    }

    public function json($data, $statusCode = 200)
    {
        $content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8', true);
        header('Content-Length: ' . strlen($content), true);

        echo $content;
    }

    public function redirect($path, $statusCode = 302)
    {
        $url = rtrim(APP_URL, '/') . '/' . ltrim($path, '/');

        http_response_code($statusCode);
        header('Location: ' . $url, true);
        header('Content-Length: 0', true);

        exit;
    }

    public function back($statusCode = 303)
    {
        $url = filter_input(INPUT_SERVER, 'HTTP_REFERER') ?: rtrim(APP_URL, '/');

        http_response_code($statusCode);
        header('Location: ' . $url, true);
        header('Content-Length: 0', true);

        exit;
    }
}
