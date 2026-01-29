<?php

namespace Services;

class Response
{
    public function send($content = '', $statusCode = 200)
    {
        http_response_code($statusCode);

        header('Content-Type: text/html; charset=utf-8', true);
        header('Content-Length: ' . strlen($content), true);

        echo $content;
    }

    public function redirectTo($path, $statusCode = 302)
    {
        http_response_code($statusCode);

        header('Location: ' . '/' . ltrim($path, '/'), true);
        header('Content-Length: 0', true);

        exit;
    }

    public function redirectToPrevious($statusCode = 302)
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $appHost = parse_url(APP_URL, PHP_URL_HOST);
        $refererHost = parse_url($referer, PHP_URL_HOST);

        if ($refererHost !== $appHost) {
            $referer = '/';
        }

        http_response_code($statusCode);

        header('Location: ' . $referer, true);
        header('Content-Length: 0', true);

        exit;
    }
}
