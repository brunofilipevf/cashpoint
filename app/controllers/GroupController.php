<?php

namespace App\Controllers;

use App\Models\Group;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class GroupController
{
    public static function index()
    {
        $groups = Group::all();
        Response::view('group/index', ['groups' => $groups]);
    }

    public static function add()
    {
        Response::view('group/add');
    }

    public static function insert()
    {
        $data = [
            'name' => Request::input('name'),
            'multiplier_factor' => Request::input('multiplier_factor'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $errors = Validator::fields($data, [
            'name' => 'required|string|min:2|max:60',
            'multiplier_factor' => 'required|numeric|min:0.01'
        ], [
            'name' => 'nome',
            'multiplier_factor' => 'fator multiplicador'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::previous();
        }

        $inserted = Group::insert($data);

        if (!$inserted) {
            Session::setFlash('danger', 'Erro ao adicionar registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro adicionado com sucesso');
        Response::redirect('/groups');
    }

    public static function edit($id)
    {
        $targetGroup = Group::get($id);

        if (!$targetGroup) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        Response::view('group/edit', ['group' => $targetGroup]);
    }

    public static function update($id)
    {
        $targetGroup = Group::get($id);

        if (!$targetGroup) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        $data = [
            'name' => Request::input('name'),
            'multiplier_factor' => Request::input('multiplier_factor'),
            'is_active' => Request::input('is_active'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $errors = Validator::fields($data, [
            'name' => 'required|string|min:2|max:60',
            'multiplier_factor' => 'required|numeric|min:0.01',
            'is_active' => 'required|in:0,1'
        ], [
            'name' => 'nome',
            'multiplier_factor' => 'fator multiplicador',
            'is_active' => 'status'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::previous();
        }

        $updated = Group::update($data, $id);

        if (!$updated) {
            Session::setFlash('danger', 'Erro ao atualizar registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro atualizado com sucesso');
        Response::redirect('/groups');
    }

    public static function delete($id)
    {
        $targetGroup = Group::get($id);

        if (!$targetGroup) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        $deleted = Group::delete($id);

        if (!$deleted) {
            Session::setFlash('danger', 'Erro ao excluir registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro excluído com sucesso');
        Response::redirect('/groups');
    }
}
