<?php

namespace App\Middlewares;

class VerifyCsrf
{
    public function __construct(
        private \Core\Request $request,
        private \Core\Response $response,
        private \Core\Session $session
    ) {}

    public function handle()
    {
        if ($this->request->method() === 'POST') {
            $token = $this->request->post('csrf_token');
            $isValid = $this->session->verifyCsrf($token);

            if (!$isValid) {
                $this->session->setFlash('danger', 'Token de segurança inválido');
                $this->response->redirect('same_uri');
            }
        }
    }
}
