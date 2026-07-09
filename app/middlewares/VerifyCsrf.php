<?php

namespace App\Middlewares;

use Core\{Request, Response, Session};

class VerifyCsrf
{
    public static function handle()
    {
        $token = Request::input('csrf_token');
        $isValid = Session::verifyCsrf($token);

        if (!$isValid) {
            Session::setFlash('danger', 'Token de segurança inválido');
            Response::redirect('same_uri');
        }
    }
}
