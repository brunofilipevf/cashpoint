<?php

namespace App\Controllers;

use App\Models\Auth;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class AuthController
{
    public static function index()
    {
        Response::view('auth/index');
    }

    public static function login()
    {
        $data = [
            'username' => Request::input('username'),
            'password' => Request::input('password')
        ];

        $errors = Validator::fields($data, [
            'username' => 'required|string',
            'password' => 'required|string'
        ], [
            'username' => 'nome de usuário',
            'password' => 'senha'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::previous();
        }

        $authId = Auth::attempt($data);

        if (!$authId) {
            Session::setFlash('danger', 'Credenciais inválidas ou usuário inativo');
            Response::previous();
        }

        Session::regenerate();
        Session::set('auth.id', $authId);
        Response::redirect('/');
    }

    public static function logout()
    {
        Session::destroy();
        Response::redirect('/login');
    }
}
