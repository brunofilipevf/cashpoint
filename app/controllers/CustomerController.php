<?php

namespace App\Controllers;

class CustomerController
{
    public function __construct(
        private \App\Models\Customer $customer,
        private \App\Models\Group $group,
        private \Core\Database $database,
        private \Core\Request $request,
        private \Core\Response $response,
        private \Core\Session $session,
        private \Core\Validator $validator
    ) {}

    public function index()
    {
        $this->response->view('customer/index', [
            'customers' => $this->customer->all()
        ]);
    }

    public function add()
    {
        $this->response->view('customer/add', [
            'groups' => $this->group->all()
        ]);
    }

    public function insert()
    {
        $requestData = [
            'cpf' => $this->request->post('cpf'),
            'fullname' => $this->request->post('fullname'),
            'email' => $this->request->post('email'),
            'phone' => $this->request->post('phone'),
            'group_id' => $this->request->post('group_id')
        ];

        $errors = $this->validator->fields($requestData, [
            'cpf' => 'required|document|unique:customer,cpf',
            'fullname' => 'string|max:60',
            'email' => 'email|unique:customer,email',
            'phone' => 'phone|unique:customer,phone',
            'group_id' => 'integer|exist:group,id'
        ], [
            'cpf' => 'CPF/CNPJ',
            'fullname' => 'nome completo',
            'email' => 'e-mail',
            'phone' => 'celular',
            'group_id' => 'grupo'
        ]);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            $this->response->redirect('same_uri');
        }

        $this->customer->insert($requestData);
        $this->session->setFlash('success', 'Cliente adicionado com sucesso');
        $this->response->redirect('/customers');
    }

    public function edit($customerId)
    {
        $customerData = $this->customer->find($customerId);

        if (!$customerData) {
            $this->response->abort(404);
        }

        $this->response->view('customer/edit', [
            'customer' => $customerData,
            'groups' => $this->group->all()
        ]);
    }

    public function update($customerId)
    {
        $customerData = $this->customer->find($customerId);

        if (!$customerData) {
            $this->response->abort(404);
        }

        $requestData = [
            'fullname' => $this->request->post('fullname'),
            'email' => $this->request->post('email'),
            'phone' => $this->request->post('phone'),
            'group_id' => $this->request->post('group_id'),
            'is_active' => $this->request->post('is_active')
        ];

        $errors = $this->validator->fields($requestData, [
            'fullname' => 'string|max:60',
            'email' => "email|unique:customer,email,{$customerId}",
            'phone' => "phone|unique:customer,phone,{$customerId}",
            'group_id' => 'integer|exist:group,id',
            'is_active' => 'required|in:0,1'
        ], [
            'fullname' => 'nome completo',
            'email' => 'e-mail',
            'phone' => 'celular',
            'group_id' => 'grupo',
            'is_active' => 'status'
        ]);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            $this->response->redirect('same_uri');
        }

        $this->customer->update($requestData, $customerId);
        $this->session->setFlash('success', 'Cliente atualizado com sucesso');
        $this->response->redirect('/customers');
    }

    public function delete($customerId)
    {
        $customerData = $this->customer->find($customerId);

        if (!$customerData) {
            $this->response->abort(404);
        }

        try {
            $this->customer->delete($customerId);
            $this->session->setFlash('success', 'Cliente excluído com sucesso');
            $this->response->redirect('/customers');
        } catch (\PDOException $e) {
            if ($e->getCode() === '1451') {
                $this->session->setFlash('danger', 'Não é possível excluir este cliente');
                $this->response->redirect('/customers/edit/' . $customerId);
            }

            $this->session->setFlash('danger', 'Erro ao excluir cliente');
            $this->response->redirect('/customers/edit/' . $customerId);
        }
    }
}
