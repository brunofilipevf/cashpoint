<?php

namespace App\Middlewares;

use Core\Request;
use Core\Response;
use Core\Session;

class ValidateCsrf
{
    public static function handle()
    {
        $token = Request::input('csrf_token');
        $isValid = Session::validateCsrf($token);

        if (!$isValid) {
            Session::setFlash('danger', 'Token de segurança inválido ou expirado');
            return Response::previous();
        }
    }
}
