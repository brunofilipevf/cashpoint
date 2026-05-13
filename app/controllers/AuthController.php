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
        return Response::view('auth/index');
    }

    public static function login()
    {
        $data = [
            'username' => Request::input('username'),
            'password' => Request::input('password')
        ];

        $rules = [
            'username' => 'required|string',
            'password' => 'required|string'
        ];

        $labels = [
            'username' => 'nome de usuário',
            'password' => 'senha'
        ];

        $errors = Validator::fields($data, $rules, $labels);

        if ($errors) {
            Session::setFlash('danger', $errors);
            return Response::previous();
        }

        $authId = Auth::attempt($data);

        if (!$authId) {
            Session::setFlash('danger', 'Credenciais inválidas ou usuário inativo');
            return Response::previous();
        }

        Session::regenerate();
        Session::set('auth.id', $authId);
        return Response::redirect('/');
    }

    public static function logout()
    {
        Session::destroy();
        return Response::redirect('/login');
    }
}
