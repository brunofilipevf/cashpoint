<?php

namespace App\Controllers;

use App\Models\Company;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class CompanyController
{
    public function __construct(
        private Company $company,
        private Response $response,
        private Request $request,
        private Session $session,
        private Validator $validator
    ) { }

    public function index()
    {
        $companies = $this->company->all();
        return $this->response->render('company/index', ['companies' => $companies]);
    }

    public function add()
    {
        return $this->response->render('company/add');
    }

    public function insert()
    {
        $data = [
            'cpf' => $this->request->input('cpf'),
            'name' => $this->request->input('name'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $rules = [
            'cpf' => 'required|document|unique:company,cpf',
            'name' => 'required|string|min:2|max:60'
        ];

        $labels = [
            'cpf' => 'CPF/CNPJ',
            'name' => 'nome'
        ];

        $errors = $this->validator->fields($data, $rules, $labels);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            return $this->response->previous();
        }

        $inserted = $this->company->insert($data);

        if (!$inserted) {
            $this->session->setFlash('danger', 'Erro ao adicionar registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro adicionado com sucesso');
        return $this->response->redirect('/companies');
    }

    public function edit($id)
    {
        $targetCompany = $this->company->get($id);

        if (!$targetCompany) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        return $this->response->render('company/edit', ['company' => $targetCompany]);
    }

    public function update($id)
    {
        $targetCompany = $this->company->get($id);

        if (!$targetCompany) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        $data = [
            'name' => $this->request->input('name'),
            'is_active' => $this->request->input('is_active'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $rules = [
            'name' => 'required|string|min:2|max:60',
            'is_active' => 'required|in:0,1'
        ];

        $labels = [
            'name' => 'nome',
            'is_active' => 'status'
        ];

        $errors = $this->validator->fields($data, $rules, $labels);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            return $this->response->previous();
        }

        $updated = $this->company->update($data, $id);

        if (!$updated) {
            $this->session->setFlash('danger', 'Erro ao atualizar registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro atualizado com sucesso');
        return $this->response->redirect('/companies');
    }

    public function delete($id)
    {
        $targetCompany = $this->company->get($id);

        if (!$targetCompany) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        $deleted = $this->company->delete($id);

        if ($deleted === '23000') {
            $this->session->setFlash('danger', 'Registro possui vínculos e não pode ser excluído');
            return $this->response->previous();
        }

        if (!$deleted) {
            $this->session->setFlash('danger', 'Erro ao excluir registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro excluído com sucesso');
        return $this->response->redirect('/companies');
    }
}
