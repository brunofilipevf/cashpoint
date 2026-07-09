<?php

namespace App\Controllers;

use App\Models\Company;
use Core\{Database, Request, Response, Session, Validator};

class CompanyController
{
    public static function index()
    {
        Response::view('company/index', [
            'companies' => Company::all()
        ]);
    }

    public static function add()
    {
        Response::view('company/add');
    }

    public static function insert()
    {
        $requestData = [
            'cpf' => Request::input('cpf'),
            'name' => Request::input('name')
        ];

        $errors = Validator::fields($requestData, [
            'cpf' => 'required|document|unique:company,cpf',
            'name' => 'required|string|max:60'
        ], [
            'cpf' => 'CPF/CNPJ',
            'name' => 'nome'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::redirect('same_uri');
        }

        Company::insert($requestData);
        Session::setFlash('success', 'Empresa adicionada com sucesso');
        Response::redirect('/companies');
    }

    public static function edit($companyId)
    {
        $companyData = Company::get($companyId);

        if (!$companyData) {
            Response::abort(404);
        }

        Response::view('company/edit', [
            'company' => $companyData
        ]);
    }

    public static function update($companyId)
    {
        $companyData = Company::get($companyId);

        if (!$companyData) {
            Response::abort(404);
        }

        $requestData = [
            'name' => Request::input('name'),
            'is_active' => Request::input('is_active')
        ];

        $errors = Validator::fields($requestData, [
            'name' => 'required|string|max:60',
            'is_active' => 'required|in:0,1'
        ], [
            'name' => 'nome',
            'is_active' => 'status'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::redirect('same_uri');
        }

        Company::update($requestData, $companyId);
        Session::setFlash('success', 'Empresa atualizada com sucesso');
        Response::redirect('/companies');
    }

    public static function delete($companyId)
    {
        $companyData = Company::get($companyId);

        if (!$companyData) {
            Response::abort(404);
        }

        if (Database::existsInTables($companyId, 'company_id', ['user'])) {
            Session::setFlash('danger', 'Não é possível excluir esta empresa');
            Response::redirect('/companies/edit/' . $companyId);
        }

        Company::delete($companyId);
        Session::setFlash('success', 'Empresa excluída com sucesso');
        Response::redirect('/companies');
    }
}
