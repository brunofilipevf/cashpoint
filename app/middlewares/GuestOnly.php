<?php

namespace App\Middlewares;

use Core\Response;
use Core\Session;

class GuestOnly
{
    public function __construct(
        private Response $response,
        private Session $session
    ) { }

    public function handle()
    {
        $authId = $this->session->get('auth.id');

        if ($authId !== null) {
            return $this->response->redirect('/');
        }
    }
}
