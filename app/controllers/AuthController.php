<?php

namespace App\Controllers;

class AuthController
{
    public function __construct(
        private \App\Models\Activity $activity,
        private \App\Models\Auth $auth,
        private \Core\Request $request,
        private \Core\Response $response,
        private \Core\Session $session,
        private \Core\Validator $validator
    ) {}

    public function index()
    {
        $this->response->view('auth/index');
    }

    public function login()
    {
        $requestData = [
            'username' => $this->request->post('username'),
            'password' => $this->request->post('password')
        ];

        $errors = $this->validator->fields($requestData, [
            'username' => 'required|string',
            'password' => 'required|string'
        ], [
            'username' => 'usuário',
            'password' => 'senha'
        ]);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            $this->response->redirect('same_uri');
        }

        $authUserId = $this->auth->login($requestData['username'], $requestData['password']);

        if (!$authUserId) {
            $this->session->setFlash('danger', 'Credenciais inválidas ou usuário inativo');
            $this->response->redirect('same_uri');
        }

        $authToken = $this->activity->create($authUserId, $this->request->ip());

        $this->session->regenerate();
        $this->session->set('auth.token', $authToken);
        $this->response->redirect('/');
    }

    public function logout()
    {
        $authToken = $this->session->get('auth.token');

        if ($authToken) {
            $this->activity->revoke($authToken);
        }

        $this->session->destroy();
        $this->response->redirect('/login');
    }
}
