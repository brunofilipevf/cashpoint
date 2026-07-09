<?php

namespace App\Controllers;

use App\Models\{Activity, Auth};
use Core\{Request, Response, Session, Validator};

class AuthController
{
    public static function index()
    {
        Response::view('auth/index');
    }

    public static function login()
    {
        $requestData = [
            'username' => Request::input('username'),
            'password' => Request::input('password')
        ];

        $errors = Validator::fields($requestData, [
            'username' => 'required|string',
            'password' => 'required|string'
        ], [
            'username' => 'usuário',
            'password' => 'senha'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::redirect('same_uri');
        }

        $authUserId = Auth::login($requestData['username'], $requestData['password']);

        if (!$authUserId) {
            Session::setFlash('danger', 'Credenciais inválidas ou usuário inativo');
            Response::redirect('same_uri');
        }

        $ip = Request::ip();
        $authToken = Activity::create($authUserId, $ip);

        Session::regenerate();
        Session::set('auth.token', $authToken);
        Response::redirect('/');
    }

    public static function logout()
    {
        $authToken = Session::get('auth.token');

        if ($authToken) {
            Activity::revoke($authToken);
        }

        Session::destroy();
        Response::redirect('/login');
    }
}
