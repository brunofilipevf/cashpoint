<?php

namespace Core;

class Response
{
    public static function send($content, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: text/plain; charset=UTF-8');
        self::getHeaders();
        exit($content);
    }

    public static function view($path, $data = [], $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: text/html; charset=UTF-8');
        self::getHeaders();
        exit(View::render($path, $data));
    }

    public static function redirect($path, $statusCode = 302)
    {
        if ($path === 'same_uri') {
            $path = Request::uri();
        }

        http_response_code($statusCode);
        header('Location: ' . $path);
        header('Cache-Control: no-store, no-cache, must-revalidate, private');
        exit();
    }

    public static function abort($statusCode)
    {
        match ($statusCode) {
            403 => self::send('Acesso negado/proibido', $statusCode),
            404 => self::send('Página não encontrada', $statusCode),
            405 => self::send('Método não permitido', $statusCode),
            429 => self::send('Muitas requisições, tente novamente mais tarde', $statusCode),
            default => self::send('Erro interno do servidor', $statusCode)
        };
    }

    private static function getHeaders()
    {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
        header('Cache-Control: no-store, no-cache, must-revalidate, private');
        header('Content-Security-Policy: ' . self::getCsp());
    }

    private static function getCsp()
    {
        return "default-src 'none'; " .
               "script-src 'self'; " .
               "style-src 'self'; " .
               "img-src 'self' data:; " .
               "font-src 'self'; " .
               "connect-src 'self'; " .
               "frame-src 'self'; " .
               "frame-ancestors 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self'; " .
               "upgrade-insecure-requests";
    }
}
