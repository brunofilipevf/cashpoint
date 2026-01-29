<?php

namespace App\Controllers;

use Services\BaseController;
use App\Models\Auth;

class AuthController extends BaseController
{
    public function index()
    {
        render('auth/index');
    }

    public function login()
    {
        $username = input('username');
        $password = input('password');

        $this->validator->field($username, 'usuário', 'required');
        $this->validator->field($password, 'senha', 'required');

        $validationErrors = $this->validator->getErrors();

        if ($validationErrors) {
            flash('danger', $validationErrors);
            back();
        }

        $userId = Auth::attemptLogin($username, $password);

        if (!$userId) {
            flash('danger', 'Credenciais inválidas ou usuário inativo.');
            back();
        }

        $this->session->regenerate();
        $this->session->set(['auth', 'id'], $userId);

        redirect('/');
    }

    public function logout()
    {
        $this->session->destroy();
        redirect('/');
    }
}
