<?php

namespace App\Controllers;

use App\Models\Customer;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class CustomerController
{
    public static function index()
    {
        $customers = Customer::all();
        Response::view('customer/index', ['customers' => $customers]);
    }

    public static function add()
    {
        Response::view('customer/add');
    }

    public static function insert()
    {
        $data = [
            'cpf' => Request::input('cpf'),
            'fullname' => Request::input('fullname'),
            'email' => Request::input('email'),
            'phone' => Request::input('phone'),
            'group_id' => Request::input('group_id'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $errors = Validator::fields($data, [
            'cpf' => 'required|document|unique:customer,cpf',
            'fullname' => 'string|min:2|max:60',
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
            Session::setFlash('danger', $errors);
            Response::previous();
        }

        $inserted = Customer::insert($data);

        if (!$inserted) {
            Session::setFlash('danger', 'Erro ao adicionar registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro adicionado com sucesso');
        Response::redirect('/customers');
    }

    public static function edit($id)
    {
        $targetCustomer = Customer::get($id);

        if (!$targetCustomer) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        Response::view('customer/edit', ['customer' => $targetCustomer]);
    }

    public static function update($id)
    {
        $targetCustomer = Customer::get($id);

        if (!$targetCustomer) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        $data = [
            'fullname' => Request::input('fullname'),
            'email' => Request::input('email'),
            'phone' => Request::input('phone'),
            'group_id' => Request::input('group_id'),
            'is_active' => Request::input('is_active'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $errors = Validator::fields($data, [
            'fullname' => 'string|min:2|max:60',
            'email' => "email|unique:customer,email,{$id}",
            'phone' => "phone|unique:customer,phone,{$id}",
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
            Session::setFlash('danger', $errors);
            Response::previous();
        }

        $updated = Customer::update($data, $id);

        if (!$updated) {
            Session::setFlash('danger', 'Erro ao atualizar registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro atualizado com sucesso');
        Response::redirect('/customers');
    }

    public static function delete($id)
    {
        $targetCustomer = Customer::get($id);

        if (!$targetCustomer) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        $deleted = Customer::delete($id);

        if (!$deleted) {
            Session::setFlash('danger', 'Erro ao excluir registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro excluído com sucesso');
        Response::redirect('/customers');
    }
}
