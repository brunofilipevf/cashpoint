<?php

namespace App\Controllers;

class CompanyController
{
    public function __construct(
        private \App\Models\Company $company,
        private \Core\Database $database,
        private \Core\Request $request,
        private \Core\Response $response,
        private \Core\Session $session,
        private \Core\Validator $validator
    ) {}

    public function index()
    {
        $this->response->view('company/index', [
            'companies' => $this->company->all()
        ]);
    }

    public function add()
    {
        $this->response->view('company/add');
    }

    public function insert()
    {
        $requestData = [
            'cpf' => $this->request->post('cpf'),
            'name' => $this->request->post('name')
        ];

        $errors = $this->validator->fields($requestData, [
            'cpf' => 'required|document|unique:company,cpf',
            'name' => 'required|string|max:60'
        ], [
            'cpf' => 'CPF/CNPJ',
            'name' => 'nome'
        ]);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            $this->response->redirect('same_uri');
        }

        $this->company->insert($requestData);
        $this->session->setFlash('success', 'Empresa adicionada com sucesso');
        $this->response->redirect('/companies');
    }

    public function edit($companyId)
    {
        $companyData = $this->company->find($companyId);

        if (!$companyData) {
            $this->response->abort(404);
        }

        $this->response->view('company/edit', [
            'company' => $companyData
        ]);
    }

    public function update($companyId)
    {
        $companyData = $this->company->find($companyId);

        if (!$companyData) {
            $this->response->abort(404);
        }

        $requestData = [
            'name' => $this->request->post('name'),
            'is_active' => $this->request->post('is_active')
        ];

        $errors = $this->validator->fields($requestData, [
            'name' => 'required|string|max:60',
            'is_active' => 'required|in:0,1'
        ], [
            'name' => 'nome',
            'is_active' => 'status'
        ]);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            $this->response->redirect('same_uri');
        }

        $this->company->update($requestData, $companyId);
        $this->session->setFlash('success', 'Empresa atualizada com sucesso');
        $this->response->redirect('/companies');
    }

    public function delete($companyId)
    {
        $companyData = $this->company->find($companyId);

        if (!$companyData) {
            $this->response->abort(404);
        }

        try {
            $this->company->delete($companyId);
            $this->session->setFlash('success', 'Empresa excluída com sucesso');
            $this->response->redirect('/companies');
        } catch (\PDOException $e) {
            if ($e->getCode() === '1451') {
                $this->session->setFlash('danger', 'Não é possível excluir esta empresa');
                $this->response->redirect('/companies/edit/' . $companyId);
            }

            $this->session->setFlash('danger', 'Erro ao excluir empresa');
            $this->response->redirect('/companies/edit/' . $companyId);
        }
    }
}
