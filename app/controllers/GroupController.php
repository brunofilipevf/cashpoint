<?php

namespace App\Controllers;

use App\Models\Group;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class GroupController
{
    public function __construct(
        private Group $group,
        private Response $response,
        private Request $request,
        private Session $session,
        private Validator $validator
    ) { }

    public function index()
    {
        $groups = $this->group->all();
        return $this->response->render('group/index', ['groups' => $groups]);
    }

    public function add()
    {
        return $this->response->render('group/add');
    }

    public function insert()
    {
        $data = [
            'name' => $this->request->input('name'),
            'multiplier_factor' => $this->request->input('multiplier_factor'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $rules = [
            'name' => 'required|string|min:2|max:60',
            'multiplier_factor' => 'required|numeric|min:0.01'
        ];

        $labels = [
            'name' => 'nome',
            'multiplier_factor' => 'fator multiplicador'
        ];

        $errors = $this->validator->fields($data, $rules, $labels);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            return $this->response->previous();
        }

        $inserted = $this->group->insert($data);

        if (!$inserted) {
            $this->session->setFlash('danger', 'Erro ao adicionar registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro adicionado com sucesso');
        return $this->response->redirect('/groups');
    }

    public function edit($id)
    {
        $targetGroup = $this->group->get($id);

        if (!$targetGroup) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        return $this->response->render('group/edit', ['group' => $targetGroup]);
    }

    public function update($id)
    {
        $targetGroup = $this->group->get($id);

        if (!$targetGroup) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        $data = [
            'name' => $this->request->input('name'),
            'multiplier_factor' => $this->request->input('multiplier_factor'),
            'is_active' => $this->request->input('is_active'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $rules = [
            'name' => 'required|string|min:2|max:60',
            'multiplier_factor' => 'required|numeric|min:0.01',
            'is_active' => 'required|in:0,1'
        ];

        $labels = [
            'name' => 'nome',
            'multiplier_factor' => 'fator multiplicador',
            'is_active' => 'status'
        ];

        $errors = $this->validator->fields($data, $rules, $labels);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            return $this->response->previous();
        }

        $updated = $this->group->update($data, $id);

        if (!$updated) {
            $this->session->setFlash('danger', 'Erro ao atualizar registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro atualizado com sucesso');
        return $this->response->redirect('/groups');
    }

    public function delete($id)
    {
        $targetGroup = $this->group->get($id);

        if (!$targetGroup) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        $deleted = $this->group->delete($id);

        if ($deleted === '23000') {
            $this->session->setFlash('danger', 'Registro possui vínculos e não pode ser excluído');
            return $this->response->previous();
        }

        if (!$deleted) {
            $this->session->setFlash('danger', 'Erro ao excluir registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro excluído com sucesso');
        return $this->response->redirect('/groups');
    }
}
