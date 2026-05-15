<?php

namespace App\Controllers;

use App\Models\Auth;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class AuthController
{
    public function __construct(
        private Auth $auth,
        private Response $response,
        private Request $request,
        private Session $session,
        private Validator $validator
    ) { }

    public function index()
    {
        return $this->response->render('auth/index');
    }

    public function login()
    {
        $data = [
            'username' => $this->request->input('username'),
            'password' => $this->request->input('password')
        ];

        $rules = [
            'username' => 'required|string',
            'password' => 'required|string'
        ];

        $labels = [
            'username' => 'nome de usuário',
            'password' => 'senha'
        ];

        $errors = $this->validator->fields($data, $rules, $labels);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            return $this->response->previous();
        }

        $authId = $this->auth->attempt($data);

        if (!$authId) {
            $this->session->setFlash('danger', 'Credenciais inválidas ou usuário inativo');
            return $this->response->previous();
        }

        $this->session->regenerate();
        $this->session->set('auth.id', $authId);
        return $this->response->redirect('/');
    }

    public function logout()
    {
        $this->session->destroy();
        return $this->response->redirect('/login');
    }
}
