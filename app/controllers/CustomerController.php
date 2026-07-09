<?php

namespace App\Controllers;

use App\Models\{Customer, Group};
use Core\{Request, Response, Session, Validator};

class CustomerController
{
    public static function index()
    {
        Response::view('customer/index', [
            'customers' => Customer::all()
        ]);
    }

    public static function add()
    {
        Response::view('customer/add', [
            'groups' => Group::all()
        ]);
    }

    public static function insert()
    {
        $requestData = [
            'cpf' => Request::input('cpf'),
            'fullname' => Request::input('fullname'),
            'email' => Request::input('email'),
            'phone' => Request::input('phone'),
            'group_id' => Request::input('group_id')
        ];

        $errors = Validator::fields($requestData, [
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
            Session::setFlash('danger', $errors);
            Response::redirect('same_uri');
        }

        Customer::insert($requestData);
        Session::setFlash('success', 'Cliente adicionado com sucesso');
        Response::redirect('/customers');
    }

    public static function edit($customerId)
    {
        $customerData = Customer::get($customerId);

        if (!$customerData) {
            Response::abort(404);
        }

        Response::view('customer/edit', [
            'customer' => $customerData,
            'groups' => Group::all()
        ]);
    }

    public static function update($customerId)
    {
        $customerData = Customer::get($customerId);

        if (!$customerData) {
            Response::abort(404);
        }

        $requestData = [
            'fullname' => Request::input('fullname'),
            'email' => Request::input('email'),
            'phone' => Request::input('phone'),
            'group_id' => Request::input('group_id'),
            'is_active' => Request::input('is_active')
        ];

        $errors = Validator::fields($requestData, [
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
            Session::setFlash('danger', $errors);
            Response::redirect('same_uri');
        }

        Customer::update($requestData, $customerId);
        Session::setFlash('success', 'Cliente atualizado com sucesso');
        Response::redirect('/customers');
    }

    public static function delete($customerId)
    {
        $customerData = Customer::get($customerId);

        if (!$customerData) {
            Response::abort(404);
        }

        if (Database::existsInTables($customerId, 'customer_id', ['score', 'redemption'])) {
            Session::setFlash('danger', 'Não é possível excluir este cliente');
            Response::redirect('/customers/edit/' . $customerId);
        }

        Customer::delete($customerId);
        Session::setFlash('success', 'Cliente excluído com sucesso');
        Response::redirect('/customers');
    }
}
