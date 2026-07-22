<?php

namespace App\Middlewares;

class VerifyApiToken
{
    public function __construct(
        private \Core\Request $request,
        private \Core\Response $response
    ) {}

    public function handle()
    {
        $headers = $this->request->headers();

        if (!isset($headers['Authorization'])) {
            $this->response->json(['error', 'Token não informado']);
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);

        if ($token === '') {
            $this->response->json(['error', 'Token não informado']);
        }

        if (!hash_equals(API_TOKEN, $token)) {
            $this->response->json(['error', 'Token inválido']);
        }
    }
}
