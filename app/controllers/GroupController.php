<?php

namespace App\Controllers;

class GroupController
{
    public function __construct(
        private \App\Models\Group $group,
        private \Core\Database $database,
        private \Core\Request $request,
        private \Core\Response $response,
        private \Core\Session $session,
        private \Core\Validator $validator
    ) {}

    public function index()
    {
        $this->response->view('group/index', [
            'groups' => $this->group->all()
        ]);
    }

    public function add()
    {
        $this->response->view('group/add');
    }

    public function insert()
    {
        $requestData = [
            'name' => $this->request->post('name'),
            'multiplier_factor' => $this->request->post('multiplier_factor')
        ];

        $errors = $this->validator->fields($requestData, [
            'name' => 'required|string|max:60',
            'multiplier_factor' => 'required|numeric|min:0.01|max:' . MAX_VALUE_LIMIT_MULTIPLIER_FACTOR
        ], [
            'name' => 'nome',
            'multiplier_factor' => 'fator multiplicador'
        ]);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            $this->response->redirect('same_uri');
        }

        $this->group->insert($requestData);
        $this->session->setFlash('success', 'Grupo adicionado com sucesso');
        $this->response->redirect('/groups');
    }

    public function edit($groupId)
    {
        $groupData = $this->group->find($groupId);

        if (!$groupData) {
            $this->response->abort(404);
        }

        $this->response->view('group/edit', [
            'group' => $groupData
        ]);
    }

    public function update($groupId)
    {
        $groupData = $this->group->find($groupId);

        if (!$groupData) {
            $this->response->abort(404);
        }

        $requestData = [
            'name' => $this->request->post('name'),
            'multiplier_factor' => $this->request->post('multiplier_factor'),
            'is_active' => $this->request->post('is_active')
        ];

        $errors = $this->validator->fields($requestData, [
            'name' => 'required|string|max:60',
            'multiplier_factor' => 'required|numeric|min:0.01|max:' . MAX_VALUE_LIMIT_MULTIPLIER_FACTOR,
            'is_active' => 'required|in:0,1'
        ], [
            'name' => 'nome',
            'multiplier_factor' => 'fator multiplicador',
            'is_active' => 'status'
        ]);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            $this->response->redirect('same_uri');
        }

        $this->group->update($requestData, $groupId);
        $this->session->setFlash('success', 'Grupo atualizado com sucesso');
        $this->response->redirect('/groups');
    }

    public function delete($groupId)
    {
        $groupData = $this->group->find($groupId);

        if (!$groupData) {
            $this->response->abort(404);
        }

        if ($this->database->existsInTables($groupId, 'group_id', ['award', 'customer'])) {
            $this->session->setFlash('danger', 'Não é possível excluir este grupo');
            $this->response->redirect('/groups/edit/' . $groupId);
        }

        $this->group->delete($groupId);
        $this->session->setFlash('success', 'Grupo excluído com sucesso');
        $this->response->redirect('/groups');
    }
}
