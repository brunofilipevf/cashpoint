<?php

namespace App\Middlewares;

use Core\Request;
use Core\Response;
use Core\Session;

class ValidateCsrf
{
    public function __construct(
        private Request $request,
        private Response $response,
        private Session $session
    ) { }

    public function handle()
    {
        $token = $this->request->input('csrf_token');
        $isValid = $this->session->validateCsrf($token);

        if (!$isValid) {
            $this->session->setFlash('danger', 'Token de segurança inválido ou expirado');
            return $this->response->previous();
        }
    }
}
