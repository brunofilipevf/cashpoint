<?php

namespace App\Controllers;

use App\Models\User;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class UserController
{
    public static function index()
    {
        $users = User::all();
        Response::view('user/index', ['users' => $users]);
    }

    public static function add()
    {
        Response::view('user/add');
    }

    public static function insert()
    {
        $data = [
            'username' => Request::input('username'),
            'password' => Request::input('password'),
            'fullname' => Request::input('fullname'),
            'level_id' => Request::input('level_id'),
            'company_id' => Request::input('company_id'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $errors = Validator::fields($data, [
            'username' => 'required|alpha|min:2|max:60|unique:user,username',
            'password' => 'required|alphanum|min:6|max:60',
            'fullname' => 'required|string|min:2|max:60',
            'level_id' => 'required|integer|exist:level,id',
            'company_id' => 'integer|exist:company,id'
        ], [
            'username' => 'nome de usuário',
            'password' => 'senha',
            'fullname' => 'nome completo',
            'level_id' => 'nível',
            'company_id' => 'empresa'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::previous();
        }

        $inserted = User::insert($data);

        if (!$inserted) {
            Session::setFlash('danger', 'Erro ao adicionar registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro adicionado com sucesso');
        Response::redirect('/users');
    }

    public static function edit($id)
    {
        $targetUser = User::get($id);

        if (!$targetUser) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        Response::view('user/edit', ['user' => $targetUser]);
    }

    public static function update($id)
    {
        $targetUser = User::get($id);

        if (!$targetUser) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        $data = [
            'password' => Request::input('password'),
            'fullname' => Request::input('fullname'),
            'level_id' => Request::input('level_id'),
            'company_id' => Request::input('company_id'),
            'is_active' => Request::input('is_active'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $errors = Validator::fields($data, [
            'password' => 'alphanum|min:6|max:60',
            'fullname' => 'required|string|min:2|max:60',
            'level_id' => 'required|integer|exist:level,id',
            'company_id' => 'integer|exist:company,id',
            'is_active' => 'required|in:0,1'
        ], [
            'password' => 'senha',
            'fullname' => 'nome completo',
            'level_id' => 'nível',
            'company_id' => 'empresa',
            'is_active' => 'status'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::previous();
        }

        $updated = User::update($data, $id);

        if (!$updated) {
            Session::setFlash('danger', 'Erro ao atualizar registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro atualizado com sucesso');
        Response::redirect('/users');
    }

    public static function delete($id)
    {
        $targetUser = User::get($id);

        if (!$targetUser) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        $authId = Session::get('auth.id');

        if ($authId == $id) {
            Session::setFlash('danger', 'Não é possível excluir o próprio usuário');
            Response::previous();
        }

        $deleted = User::delete($id);

        if (!$deleted) {
            Session::setFlash('danger', 'Erro ao excluir registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro excluído com sucesso');
        Response::redirect('/users');
    }
}
