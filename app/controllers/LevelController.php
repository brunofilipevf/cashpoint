<?php

namespace App\Controllers;

use App\Models\Level;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class LevelController
{
    public function __construct(
        private Level $level,
        private Response $response,
        private Request $request,
        private Session $session,
        private Validator $validator
    ) { }

    public function index()
    {
        $levels = $this->level->all();
        return $this->response->render('level/index', ['levels' => $levels]);
    }

    public function add()
    {
        return $this->response->render('level/add');
    }

    public function insert()
    {
        $data = [
            'name' => $this->request->input('name'),
            'hierarchy' => $this->request->input('hierarchy'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $rules = [
            'name' => 'required|string|min:2|max:60',
            'hierarchy' => 'required|integer|min:1'
        ];

        $labels = [
            'name' => 'nome',
            'hierarchy' => 'hierarquia'
        ];

        $errors = $this->validator->fields($data, $rules, $labels);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            return $this->response->previous();
        }

        $inserted = $this->level->insert($data);

        if (!$inserted) {
            $this->session->setFlash('danger', 'Erro ao adicionar registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro adicionado com sucesso');
        return $this->response->redirect('/levels');
    }

    public function edit($id)
    {
        $targetLevel = $this->level->get($id);

        if (!$targetLevel) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        return $this->response->render('level/edit', ['level' => $targetLevel]);
    }

    public function update($id)
    {
        $targetLevel = $this->level->get($id);

        if (!$targetLevel) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        $data = [
            'name' => $this->request->input('name'),
            'hierarchy' => $this->request->input('hierarchy'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $rules = [
            'name' => 'required|string|min:2|max:60',
            'hierarchy' => 'required|integer|min:1'
        ];

        $labels = [
            'name' => 'nome',
            'hierarchy' => 'hierarquia'
        ];

        $errors = $this->validator->fields($data, $rules, $labels);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            return $this->response->previous();
        }

        $updated = $this->level->update($data, $id);

        if (!$updated) {
            $this->session->setFlash('danger', 'Erro ao atualizar registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro atualizado com sucesso');
        return $this->response->redirect('/levels');
    }

    public function delete($id)
    {
        $targetLevel = $this->level->get($id);

        if (!$targetLevel) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        $deleted = $this->level->delete($id);

        if ($deleted === '23000') {
            $this->session->setFlash('danger', 'Registro possui vínculos e não pode ser excluído');
            return $this->response->previous();
        }

        if (!$deleted) {
            $this->session->setFlash('danger', 'Erro ao excluir registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro excluído com sucesso');
        return $this->response->redirect('/levels');
    }
}
