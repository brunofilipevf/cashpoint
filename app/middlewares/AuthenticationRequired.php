<?php

namespace App\Middlewares;

use Services\BaseMiddleware;

class AuthenticationRequired extends BaseMiddleware
{
    public function handle()
    {
        if (!$this->session->get(['auth', 'id'])) {
            redirect('/login');
        }
    }
}
