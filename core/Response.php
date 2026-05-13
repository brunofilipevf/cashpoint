<?php

namespace Core;

class Response
{
    private static $headers = [];

    public static function send($content, $statusCode = 200)
    {
        http_response_code($statusCode);

        self::setHeader('Content-Type', 'text/html; charset=UTF-8');
        self::setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        self::setHeader('X-Content-Type-Options', 'nosniff');
        self::setHeader('X-Frame-Options', 'DENY');
        self::setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        self::setHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');
        self::setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        self::setHeader('Content-Security-Policy', self::getCsp());
        self::getHeaders();

        exit($content);
    }

    public static function view($path, $data = [], $statusCode = 200)
    {
        $content = View::render($path, $data);
        self::send($content, $statusCode);
    }

    public static function redirect($path, $statusCode = 302)
    {
        http_response_code($statusCode);

        self::setHeader('Location', $path);
        self::setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        self::setHeader('Pragma', 'no-cache');
        self::getHeaders();

        exit;
    }

    public static function previous($statusCode = 302)
    {
        $previousUri = Session::get('previous_uri');
        self::redirect($previousUri, $statusCode);
    }

    private static function setHeader($key, $value)
    {
        self::$headers[$key] = $value;
    }

    private static function getHeaders()
    {
        foreach (self::$headers as $key => $value) {
            header("{$key}: {$value}");
        }
    }

    private static function getCsp()
    {
        return "default-src 'none'; " .
               "script-src 'self'; " .
               "style-src 'self'; " .
               "img-src 'self'; " .
               "font-src 'self'; " .
               "connect-src 'self'; " .
               "frame-src 'self'; " .
               "frame-ancestors 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self'; " .
               "upgrade-insecure-requests";
    }
}
