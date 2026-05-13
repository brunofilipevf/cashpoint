<?php

namespace App\Middlewares;

use Core\Response;
use Core\Session;

class AuthOnly
{
    public static function handle()
    {
        $authId = Session::get('auth.id');

        if ($authId === null) {
            Response::redirect('/login');
        }
    }
}
