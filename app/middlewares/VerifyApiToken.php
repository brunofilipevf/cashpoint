<?php

namespace App\Middlewares;

class VerifyApiToken
{
    public function __construct(
        private \Core\Response $response
    ) {}

    public function handle()
    {
        // --------------------------------------------------
        // Obtém o token do cabeçalho Authorization
        // --------------------------------------------------

        $token = '';

        $headers = getallheaders();

        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
        }

        // --------------------------------------------------
        // Verifica se o token foi informado
        // --------------------------------------------------

        if (!is_string($token) || $token === '' || $token === null) {
            $this->response->json(['error', 'Token não informado']);
        }

        // --------------------------------------------------
        // Valida o token contra a constante API_TOKEN
        // --------------------------------------------------

        if (!hash_equals(API_TOKEN, $token)) {
            $this->response->json(['error', 'Token inválido']);
        }
    }
}
