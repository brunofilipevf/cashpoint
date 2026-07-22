<?php

namespace App\Controllers;

class UserController
{
    public function __construct(
        private \App\Models\Auth $auth,
        private \App\Models\Company $company,
        private \App\Models\Level $level,
        private \App\Models\User $user,
        private \Core\Database $database,
        private \Core\Request $request,
        private \Core\Response $response,
        private \Core\Session $session,
        private \Core\Validator $validator
    ) {}

    public function index()
    {
        $this->response->view('user/index', [
            'users' => $this->user->all()
        ]);
    }

    public function add()
    {
        $this->response->view('user/add', [
            'levels' => $this->level->all(),
            'companies' => $this->company->all()
        ]);
    }

    public function insert()
    {
        $authUserData = $this->auth->stored();

        $requestData = [
            'username' => $this->request->post('username'),
            'password' => $this->request->post('password'),
            'fullname' => $this->request->post('fullname'),
            'level_id' => $this->request->post('level_id'),
            'company_id' => $this->request->post('company_id')
        ];

        $errors = $this->validator->fields($requestData, [
            'username' => 'required|alpha|max:60|unique:user,username',
            'password' => 'required|alphanum|min:6|max:60',
            'fullname' => 'required|string|max:60',
            'level_id' => 'required|integer|exist:level,id',
            'company_id' => 'integer|exist:company,id'
        ], [
            'username' => 'usuário',
            'password' => 'senha',
            'fullname' => 'nome completo',
            'level_id' => 'nível',
            'company_id' => 'empresa'
        ]);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            $this->response->redirect('same_uri');
        }

        $levelData = $this->level->find($requestData['level_id']);

        if ($authUserData['hierarchy'] <= $levelData['hierarchy']) {
            $this->session->setFlash('danger', 'Você não pode atribuir um nível igual ou superior ao seu');
            $this->response->redirect('same_uri');
        }

        $this->user->insert($requestData);
        $this->session->setFlash('success', 'Usuário adicionado com sucesso');
        $this->response->redirect('/users');
    }

    public function edit($userId)
    {
        $userData = $this->user->find($userId);

        if (!$userData) {
            $this->response->abort(404);
        }

        $this->response->view('user/edit', [
            'user' => $userData,
            'levels' => $this->level->all(),
            'companies' => $this->company->all()
        ]);
    }

    public function update($userId)
    {
        $authUserData = $this->auth->stored();
        $userData = $this->user->find($userId);

        if (!$userData) {
            $this->response->abort(404);
        }

        if ($authUserData['id'] !== $userId) {
            if ($authUserData['hierarchy'] <= $userData['hierarchy']) {
                $this->session->setFlash('danger', 'Você não pode editar este usuário');
                $this->response->redirect('same_uri');
            }
        }

        $requestData = [
            'password' => $this->request->post('password'),
            'fullname' => $this->request->post('fullname'),
            'level_id' => $this->request->post('level_id'),
            'company_id' => $this->request->post('company_id'),
            'is_active' => $this->request->post('is_active')
        ];

        $errors = $this->validator->fields($requestData, [
            'password' => 'alphanum|min:6|max:60',
            'fullname' => 'required|string|max:60',
            'level_id' => 'required|integer|exist:level,id',
            'company_id' => 'integer|exist:company,id',
            'is_active' => 'required|in:0,1'
        ], [
            'password' => 'senha',
            'fullname' => 'nome completo',
            'level_id' => 'nível',
            'company_id' => 'empresa',
            'is_active' => 'status'
        ]);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            $this->response->redirect('same_uri');
        }

        if ($authUserData['id'] === $userId) {
            $requestData['level_id'] = $authUserData['level_id'];
            $requestData['is_active'] = $authUserData['is_active'];
        }

        if ($authUserData['id'] !== $userId) {
            $levelData = $this->level->find($requestData['level_id']);

            if ($authUserData['hierarchy'] <= $levelData['hierarchy']) {
                $this->session->setFlash('danger', 'Você não pode atribuir um nível igual ou superior ao seu');
                $this->response->redirect('same_uri');
            }
        }

        $this->user->update($requestData, $userId);
        $this->session->setFlash('success', 'Usuário atualizado com sucesso');
        $this->response->redirect('/users');
    }

    public function delete($userId)
    {
        $authUserData = $this->auth->stored();
        $userData = $this->user->find($userId);

        if (!$userData) {
            $this->response->abort(404);
        }

        if ($authUserData['id'] === $userId) {
            $this->session->setFlash('danger', 'Você não pode excluir seu próprio usuário');
            $this->response->redirect('/users/edit/' . $userId);
        }

        if ($authUserData['hierarchy'] <= $userData['hierarchy']) {
            $this->session->setFlash('danger', 'Você não pode excluir este usuário');
            $this->response->redirect('/users/edit/' . $userId);
        }

        try {
            $this->user->delete($userId);
            $this->session->setFlash('success', 'Usuário excluído com sucesso');
            $this->response->redirect('/users');
        } catch (\PDOException $e) {
            if ($e->getCode() === '1451') {
                $this->session->setFlash('danger', 'Não é possível excluir este usuário');
                $this->response->redirect('/users/edit/' . $userId);
            }

            $this->session->setFlash('danger', 'Erro ao excluir usuário');
            $this->response->redirect('/users/edit/' . $userId);
        }
    }
}
