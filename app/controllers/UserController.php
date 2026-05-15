<?php

namespace App\Controllers;

use App\Models\Company;
use App\Models\Level;
use App\Models\User;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class UserController
{
    public function __construct(
        private Company $company,
        private Level $level,
        private User $user,
        private Response $response,
        private Request $request,
        private Session $session,
        private Validator $validator
    ) { }

    public function index()
    {
        $users = $this->user->all();
        return $this->response->render('user/index', ['users' => $users]);
    }

    public function add()
    {
        $levels = $this->level->all();
        $companies = $this->company->all();
        return $this->response->render('user/add', [
            'levels' => $levels,
            'companies' => $companies
        ]);
    }

    public function insert()
    {
        $data = [
            'username' => $this->request->input('username'),
            'password' => $this->request->input('password'),
            'fullname' => $this->request->input('fullname'),
            'level_id' => $this->request->input('level_id'),
            'company_id' => $this->request->input('company_id'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $rules = [
            'username' => 'required|alpha|min:2|max:60|unique:user,username',
            'password' => 'required|alphanum|min:6|max:60',
            'fullname' => 'required|string|min:2|max:60',
            'level_id' => 'required|integer|exist:level,id',
            'company_id' => 'integer|exist:company,id'
        ];

        $labels = [
            'username' => 'nome de usuário',
            'password' => 'senha',
            'fullname' => 'nome completo',
            'level_id' => 'nível',
            'company_id' => 'empresa'
        ];

        $errors = $this->validator->fields($data, $rules, $labels);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            return $this->response->previous();
        }

        $inserted = $this->user->insert($data);

        if (!$inserted) {
            $this->session->setFlash('danger', 'Erro ao adicionar registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro adicionado com sucesso');
        return $this->response->redirect('/users');
    }

    public function edit($id)
    {
        $targetUser = $this->user->get($id);

        if (!$targetUser) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        $levels = $this->level->all();
        $companies = $this->company->all();
        return $this->response->render('user/edit', [
            'user' => $targetUser,
            'levels' => $levels,
            'companies' => $companies
        ]);
    }

    public function update($id)
    {
        $targetUser = $this->user->get($id);

        if (!$targetUser) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        $data = [
            'password' => $this->request->input('password'),
            'fullname' => $this->request->input('fullname'),
            'level_id' => $this->request->input('level_id'),
            'company_id' => $this->request->input('company_id'),
            'is_active' => $this->request->input('is_active'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $rules = [
            'password' => 'alphanum|min:6|max:60',
            'fullname' => 'required|string|min:2|max:60',
            'level_id' => 'required|integer|exist:level,id',
            'company_id' => 'integer|exist:company,id',
            'is_active' => 'required|in:0,1'
        ];

        $labels = [
            'password' => 'senha',
            'fullname' => 'nome completo',
            'level_id' => 'nível',
            'company_id' => 'empresa',
            'is_active' => 'status'
        ];

        $errors = $this->validator->fields($data, $rules, $labels);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            return $this->response->previous();
        }

        $updated = $this->user->update($data, $id);

        if (!$updated) {
            $this->session->setFlash('danger', 'Erro ao atualizar registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro atualizado com sucesso');
        return $this->response->redirect('/users');
    }

    public function delete($id)
    {
        $targetUser = $this->user->get($id);

        if (!$targetUser) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        $authId = $this->session->get('auth.id');

        if ($authId === (int) $id) {
            $this->session->setFlash('danger', 'Não é possível excluir o próprio usuário');
            return $this->response->previous();
        }

        $deleted = $this->user->delete($id);

        if ($deleted === 23000) {
            $this->session->setFlash('danger', 'Registro possui vínculos e não pode ser excluído');
            return $this->response->previous();
        }

        if (!$deleted) {
            $this->session->setFlash('danger', 'Erro ao excluir registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro excluído com sucesso');
        return $this->response->redirect('/users');
    }
}
