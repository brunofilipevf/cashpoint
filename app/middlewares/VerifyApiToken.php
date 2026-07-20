<?php

namespace App\Middlewares;

class VerifyApiToken
{
    public function __construct(
        private \Core\Response $response
    ) {}

    public function handle()
    {
        $token = '';

        $headers = getallheaders();

        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
        }

        if (!is_string($token) || $token === '' || $token === null) {
            $this->response->json(['error', 'Token não informado']);
        }

        if (!hash_equals(API_TOKEN, $token)) {
            $this->response->json(['error', 'Token inválido']);
        }
    }
}
