<?php

namespace Core;

class Response
{
    public function __construct(
        private Request $request,
        private View $view
    ) {}

    public function send($content, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: text/plain; charset=UTF-8');
        $this->getHeaders();
        exit($content);
    }

    public function json($content, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        $this->getHeaders();
        exit(json_encode($data));
    }

    public function view($path, $data = [], $status = 200)
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=UTF-8');
        $this->getHeaders();
        exit($this->view->render($path, $data));
    }

    public function abort($status)
    {
        match ($status) {
            403 => $this->send('Acesso negado/proibido', $status),
            404 => $this->send('Página não encontrada', $status),
            405 => $this->send('Método não permitido', $status),
            429 => $this->send('Muitas requisições, tente novamente mais tarde', $status),
            default => $this->send('Erro interno do servidor', $status)
        };
    }

    public function redirect($path, $status = 302)
    {
        if ($path === 'same_uri') {
            $path = $this->request->uri();
        }

        http_response_code($status);
        header('Location: ' . $path);
        header('Cache-Control: no-store, no-cache, must-revalidate, private');
        exit();
    }

    private function getHeaders()
    {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
        header('Cache-Control: no-store, no-cache, must-revalidate, private');
        header('Content-Security-Policy: ' . $this->getCsp());
    }

    private function getCsp()
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
