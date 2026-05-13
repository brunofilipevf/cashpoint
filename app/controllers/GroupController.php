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
        return Response::view('group/index', ['groups' => $groups]);
    }

    public static function add()
    {
        return Response::view('group/add');
    }

    public static function insert()
    {
        $data = [
            'name' => Request::input('name'),
            'multiplier_factor' => Request::input('multiplier_factor'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $rules = [
            'name' => 'required|string|min:2|max:60',
            'multiplier_factor' => 'required|numeric|min:0.01'
        ];

        $labels = [
            'name' => 'nome',
            'multiplier_factor' => 'fator multiplicador'
        ];

        $errors = Validator::fields($data, $rules, $labels);

        if ($errors) {
            Session::setFlash('danger', $errors);
            return Response::previous();
        }

        $inserted = Group::insert($data);

        if (!$inserted) {
            Session::setFlash('danger', 'Erro ao adicionar registro');
            return Response::previous();
        }

        Session::setFlash('success', 'Registro adicionado com sucesso');
        return Response::redirect('/groups');
    }

    public static function edit($id)
    {
        $targetGroup = Group::get($id);

        if (!$targetGroup) {
            Session::setFlash('danger', 'Registro não encontrado');
            return Response::previous();
        }

        return Response::view('group/edit', ['group' => $targetGroup]);
    }

    public static function update($id)
    {
        $targetGroup = Group::get($id);

        if (!$targetGroup) {
            Session::setFlash('danger', 'Registro não encontrado');
            return Response::previous();
        }

        $data = [
            'name' => Request::input('name'),
            'multiplier_factor' => Request::input('multiplier_factor'),
            'is_active' => Request::input('is_active'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $rules = [
            'name' => 'required|string|min:2|max:60',
            'multiplier_factor' => 'required|numeric|min:0.01',
            'is_active' => 'required|in:0,1'
        ];

        $labels = [
            'name' => 'nome',
            'multiplier_factor' => 'fator multiplicador',
            'is_active' => 'status'
        ];

        $errors = Validator::fields($data, $rules, $labels);

        if ($errors) {
            Session::setFlash('danger', $errors);
            return Response::previous();
        }

        $updated = Group::update($data, $id);

        if (!$updated) {
            Session::setFlash('danger', 'Erro ao atualizar registro');
            return Response::previous();
        }

        Session::setFlash('success', 'Registro atualizado com sucesso');
        return Response::redirect('/groups');
    }

    public static function delete($id)
    {
        $targetGroup = Group::get($id);

        if (!$targetGroup) {
            Session::setFlash('danger', 'Registro não encontrado');
            return Response::previous();
        }

        $deleted = Group::delete($id);

        if (!$deleted) {
            Session::setFlash('danger', 'Erro ao excluir registro');
            return Response::previous();
        }

        Session::setFlash('success', 'Registro excluído com sucesso');
        return Response::redirect('/groups');
    }
}
