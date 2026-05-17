<?php

namespace App\Controllers;

use App\Models\Customer;
use App\Models\Group;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class CustomerController
{
    public function __construct(
        private Customer $customer,
        private Group $group,
        private Response $response,
        private Request $request,
        private Session $session,
        private Validator $validator
    ) { }

    public function index()
    {
        $customers = $this->customer->all();
        return $this->response->render('customer/index', ['customers' => $customers]);
    }

    public function add()
    {
        $groups = $this->group->all();
        return $this->response->render('customer/add', ['groups' => $groups]);
    }

    public function insert()
    {
        $data = [
            'cpf' => $this->request->input('cpf'),
            'fullname' => $this->request->input('fullname'),
            'email' => $this->request->input('email'),
            'phone' => $this->request->input('phone'),
            'group_id' => $this->request->input('group_id'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $rules = [
            'cpf' => 'required|document|unique:customer,cpf',
            'fullname' => 'string|min:2|max:60',
            'email' => 'email|unique:customer,email',
            'phone' => 'phone|unique:customer,phone',
            'group_id' => 'integer|exist:group,id'
        ];

        $labels = [
            'cpf' => 'CPF/CNPJ',
            'fullname' => 'nome completo',
            'email' => 'e-mail',
            'phone' => 'celular',
            'group_id' => 'grupo'
        ];

        $errors = $this->validator->fields($data, $rules, $labels);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            return $this->response->previous();
        }

        $inserted = $this->customer->insert($data);

        if (!$inserted) {
            $this->session->setFlash('danger', 'Erro ao adicionar registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro adicionado com sucesso');
        return $this->response->redirect('/customers');
    }

    public function edit($id)
    {
        $targetCustomer = $this->customer->get($id);

        if (!$targetCustomer) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        $groups = $this->group->all();
        $balance = $this->customer->getBalance($id);
        return $this->response->render('customer/edit', [
            'customer' => $targetCustomer,
            'groups' => $groups,
            'balance' => $balance
        ]);
    }

    public function update($id)
    {
        $targetCustomer = $this->customer->get($id);

        if (!$targetCustomer) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        $data = [
            'fullname' => $this->request->input('fullname'),
            'email' => $this->request->input('email'),
            'phone' => $this->request->input('phone'),
            'group_id' => $this->request->input('group_id'),
            'is_active' => $this->request->input('is_active'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $rules = [
            'fullname' => 'string|min:2|max:60',
            'email' => "email|unique:customer,email,{$id}",
            'phone' => "phone|unique:customer,phone,{$id}",
            'group_id' => 'integer|exist:group,id',
            'is_active' => 'required|in:0,1'
        ];

        $labels = [
            'fullname' => 'nome completo',
            'email' => 'e-mail',
            'phone' => 'celular',
            'group_id' => 'grupo',
            'is_active' => 'status'
        ];

        $errors = $this->validator->fields($data, $rules, $labels);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            return $this->response->previous();
        }

        $updated = $this->customer->update($data, $id);

        if (!$updated) {
            $this->session->setFlash('danger', 'Erro ao atualizar registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro atualizado com sucesso');
        return $this->response->redirect('/customers');
    }

    public function delete($id)
    {
        $targetCustomer = $this->customer->get($id);

        if (!$targetCustomer) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        $deleted = $this->customer->delete($id);

        if ($deleted === '23000') {
            $this->session->setFlash('danger', 'Registro possui vínculos e não pode ser excluído');
            return $this->response->previous();
        }

        if (!$deleted) {
            $this->session->setFlash('danger', 'Erro ao excluir registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro excluído com sucesso');
        return $this->response->redirect('/customers');
    }
}
