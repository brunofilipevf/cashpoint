<?php

namespace App\Middlewares;

use Services\BaseMiddleware;

class ValidateCsrf extends BaseMiddleware
{
    public function handle()
    {
        if (!validateCsrf(input('token'))) {
            flash('danger', 'Token de segurança inválido');
            back();
        }
    }
}
