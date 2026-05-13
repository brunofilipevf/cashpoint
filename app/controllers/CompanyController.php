<?php

namespace App\Controllers;

use App\Models\Company;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class CompanyController
{
    public static function index()
    {
        $companies = Company::all();
        Response::view('company/index', ['companies' => $companies]);
    }

    public static function add()
    {
        Response::view('company/add');
    }

    public static function insert()
    {
        $data = [
            'cpf' => Request::input('cpf'),
            'name' => Request::input('name'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $errors = Validator::fields($data, [
            'cpf' => 'required|document|unique:company,cpf',
            'name' => 'required|string|min:2|max:60'
        ], [
            'cpf' => 'CPF/CNPJ',
            'name' => 'nome'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::previous();
        }

        $inserted = Company::insert($data);

        if (!$inserted) {
            Session::setFlash('danger', 'Erro ao adicionar registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro adicionado com sucesso');
        Response::redirect('/companies');
    }

    public static function edit($id)
    {
        $targetCompany = Company::get($id);

        if (!$targetCompany) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        Response::view('company/edit', ['company' => $targetCompany]);
    }

    public static function update($id)
    {
        $targetCompany = Company::get($id);

        if (!$targetCompany) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        $data = [
            'name' => Request::input('name'),
            'is_active' => Request::input('is_active'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $errors = Validator::fields($data, [
            'name' => 'required|string|min:2|max:60',
            'is_active' => 'required|in:0,1'
        ], [
            'name' => 'nome',
            'is_active' => 'status'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::previous();
        }

        $updated = Company::update($data, $id);

        if (!$updated) {
            Session::setFlash('danger', 'Erro ao atualizar registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro atualizado com sucesso');
        Response::redirect('/companies');
    }

    public static function delete($id)
    {
        $targetCompany = Company::get($id);

        if (!$targetCompany) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        $deleted = Company::delete($id);

        if (!$deleted) {
            Session::setFlash('danger', 'Erro ao excluir registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro excluído com sucesso');
        Response::redirect('/companies');
    }
}
