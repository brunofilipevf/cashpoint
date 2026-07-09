<?php

namespace App\Controllers;

use App\Models\Group;
use Core\{Database, Request, Response, Session, Validator};

class GroupController
{
    public static function index()
    {
        Response::view('group/index', [
            'groups' => Group::all()
        ]);
    }

    public static function add()
    {
        Response::view('group/add');
    }

    public static function insert()
    {
        $requestData = [
            'name' => Request::input('name'),
            'multiplier_factor' => Request::input('multiplier_factor')
        ];

        $errors = Validator::fields($requestData, [
            'name' => 'required|string|max:60',
            'multiplier_factor' => 'required|numeric|min:0.01|max:' . MAX_VALUE_LIMIT_MULTIPLIER_FACTOR
        ], [
            'name' => 'nome',
            'multiplier_factor' => 'fator multiplicador'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::redirect('same_uri');
        }

        Group::insert($requestData);
        Session::setFlash('success', 'Grupo adicionado com sucesso');
        Response::redirect('/groups');
    }

    public static function edit($groupId)
    {
        $groupData = Group::get($groupId);

        if (!$groupData) {
            Response::abort(404);
        }

        Response::view('group/edit', [
            'group' => $groupData
        ]);
    }

    public static function update($groupId)
    {
        $groupData = Group::get($groupId);

        if (!$groupData) {
            Response::abort(404);
        }

        $requestData = [
            'name' => Request::input('name'),
            'multiplier_factor' => Request::input('multiplier_factor'),
            'is_active' => Request::input('is_active')
        ];

        $errors = Validator::fields($requestData, [
            'name' => 'required|string|max:60',
            'multiplier_factor' => 'required|numeric|min:0.01|max:' . MAX_VALUE_LIMIT_MULTIPLIER_FACTOR,
            'is_active' => 'required|in:0,1'
        ], [
            'name' => 'nome',
            'multiplier_factor' => 'fator multiplicador',
            'is_active' => 'status'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::redirect('same_uri');
        }

        Group::update($requestData, $groupId);
        Session::setFlash('success', 'Grupo atualizado com sucesso');
        Response::redirect('/groups');
    }

    public static function delete($groupId)
    {
        $groupData = Group::get($groupId);

        if (!$groupData) {
            Response::abort(404);
        }

        if (Database::existsInTables($groupId, 'group_id', ['customer', 'award'])) {
            Session::setFlash('danger', 'Não é possível excluir este grupo');
            Response::redirect('/groups/edit/' . $groupId);
        }

        Group::delete($groupId);
        Session::setFlash('success', 'Grupo excluído com sucesso');
        Response::redirect('/groups');
    }
}
