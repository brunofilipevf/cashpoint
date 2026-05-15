<?php

namespace Core;

class Response
{
    private $headers = [];

    public function __construct(
        private Container $container
    ) { }

    public function send($content, $statusCode = 200)
    {
        http_response_code($statusCode);

        $this->setHeader('Content-Type', 'text/html; charset=UTF-8');
        $this->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        $this->setHeader('X-Content-Type-Options', 'nosniff');
        $this->setHeader('X-Frame-Options', 'DENY');
        $this->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->setHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');
        $this->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        $this->setHeader('Content-Security-Policy', $this->getCsp());
        $this->getHeaders();

        exit($content);
    }

    public function render($path, $data = [], $statusCode = 200)
    {
        $content = $this->view()->render($path, $data);
        $this->send($content, $statusCode);
    }

    public function redirect($path, $statusCode = 302)
    {
        http_response_code($statusCode);

        $this->setHeader('Location', $path);
        $this->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        $this->setHeader('Pragma', 'no-cache');
        $this->getHeaders();

        exit;
    }

    public function previous($statusCode = 302)
    {
        $previousUri = $this->session()->get('previous_uri');
        $this->redirect($previousUri, $statusCode);
    }

    private function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    private function getHeaders()
    {
        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }
    }

    private function getCsp()
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

    private function session()
    {
        return $this->container->get(Session::class);
    }

    private function view()
    {
        return $this->container->get(View::class);
    }
}
