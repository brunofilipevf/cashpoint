<?php

namespace App\Controllers;

use App\Models\{Auth, Company, Level, User};
use Core\{Database, Request, Response, Session, Validator};

class UserController
{
    public static function index()
    {
        Response::view('user/index', [
            'users' => User::all()
        ]);
    }

    public static function add()
    {
        Response::view('user/add', [
            'levels' => Level::all(),
            'companies' => Company::all()
        ]);
    }

    public static function insert()
    {
        $authUserData = Auth::stored();

        $requestData = [
            'username' => Request::input('username'),
            'password' => Request::input('password'),
            'fullname' => Request::input('fullname'),
            'level_id' => Request::input('level_id'),
            'company_id' => Request::input('company_id')
        ];

        $errors = Validator::fields($requestData, [
            'username' => 'required|alpha|max:60|unique:user,username',
            'password' => 'required|alphanum|min:6|max:60',
            'fullname' => 'required|string|max:60',
            'level_id' => 'required|integer|exist:level,id',
            'company_id' => 'integer|exist:company,id'
        ], [
            'username' => 'usuário',
            'password' => 'senha',
            'fullname' => 'nome completo',
            'level_id' => 'nível',
            'company_id' => 'empresa'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::redirect('same_uri');
        }

        $levelData = Level::get($requestData['level_id']);

        if ($authUserData['hierarchy'] <= $levelData['hierarchy']) {
            Session::setFlash('danger', 'Você não pode atribuir um nível igual ou superior ao seu');
            Response::redirect('same_uri');
        }

        User::insert($requestData);
        Session::setFlash('success', 'Usuário adicionado com sucesso');
        Response::redirect('/users');
    }

    public static function edit($userId)
    {
        $userData = User::get($userId);

        if (!$userData) {
            Response::abort(404);
        }

        Response::view('user/edit', [
            'user' => $userData,
            'levels' => Level::all(),
            'companies' => Company::all()
        ]);
    }

    public static function update($userId)
    {
        $userData = User::get($userId);

        if (!$userData) {
            Response::abort(404);
        }

        $authUserData = Auth::stored();

        if ($authUserData['id'] !== $userId) {
            if ($authUserData['hierarchy'] <= $userData['hierarchy']) {
                Session::setFlash('danger', 'Você não pode editar este usuário');
                Response::redirect('same_uri');
            }
        }

        $requestData = [
            'password' => Request::input('password'),
            'fullname' => Request::input('fullname'),
            'level_id' => Request::input('level_id'),
            'company_id' => Request::input('company_id'),
            'is_active' => Request::input('is_active')
        ];

        $errors = Validator::fields($requestData, [
            'password' => 'alphanum|min:6|max:60',
            'fullname' => 'required|string|max:60',
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
            Response::redirect('same_uri');
        }

        if ($authUserData['id'] === $userId) {
            $requestData['level_id'] = $authUserData['level_id'];
            $requestData['is_active'] = $authUserData['is_active'];
        }

        if ($authUserData['id'] !== $userId) {
            $levelData = Level::get($requestData['level_id']);

            if ($authUserData['hierarchy'] <= $levelData['hierarchy']) {
                Session::setFlash('danger', 'Você não pode atribuir um nível igual ou superior ao seu');
                Response::redirect('same_uri');
            }
        }

        User::update($requestData, $userId);
        Session::setFlash('success', 'Usuário atualizado com sucesso');
        Response::redirect('/users');
    }

    public static function delete($userId)
    {
        $userData = User::get($userId);

        if (!$userData) {
            Response::abort(404);
        }

        $authUserData = Auth::stored();

        if ($authUserData['id'] === $userId) {
            Session::setFlash('danger', 'Você não pode excluir seu próprio usuário');
            Response::redirect('/users/edit/' . $userId);
        }

        if ($authUserData['hierarchy'] <= $userData['hierarchy']) {
            Session::setFlash('danger', 'Você não pode excluir este usuário');
            Response::redirect('/users/edit/' . $userId);
        }

        if (Database::existsInTables($userId, 'user_id', ['activity', 'score', 'redemption'])) {
            Session::setFlash('danger', 'Não é possível excluir este usuário');
            Response::redirect('/users/edit/' . $userId);
        }

        User::delete($userId);
        Session::setFlash('success', 'Usuário excluído com sucesso');
        Response::redirect('/users');
    }
}
