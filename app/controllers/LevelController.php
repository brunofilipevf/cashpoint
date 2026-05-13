<?php

namespace App\Controllers;

use App\Models\Level;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class LevelController
{
    public static function index()
    {
        $levels = Level::all();
        Response::view('level/index', ['levels' => $levels]);
    }

    public static function add()
    {
        Response::view('level/add');
    }

    public static function insert()
    {
        $data = [
            'name' => Request::input('name'),
            'hierarchy' => Request::input('hierarchy'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $errors = Validator::fields($data, [
            'name' => 'required|string|min:2|max:60',
            'hierarchy' => 'required|integer|min:1'
        ], [
            'name' => 'nome',
            'hierarchy' => 'heirarquia'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::previous();
        }

        $inserted = Level::insert($data);

        if (!$inserted) {
            Session::setFlash('danger', 'Erro ao adicionar registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro adicionado com sucesso');
        Response::redirect('/levels');
    }

    public static function edit($id)
    {
        $targetLevel = Level::get($id);

        if (!$targetLevel) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        Response::view('level/edit', ['level' => $targetLevel]);
    }

    public static function update($id)
    {
        $targetLevel = Level::get($id);

        if (!$targetLevel) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        $data = [
            'name' => Request::input('name'),
            'hierarchy' => Request::input('hierarchy'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $errors = Validator::fields($data, [
            'name' => 'required|string|min:2|max:60',
            'hierarchy' => 'required|integer|min:1'
        ], [
            'name' => 'nome',
            'hierarchy' => 'heirarquia'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::previous();
        }

        $updated = Level::update($data, $id);

        if (!$updated) {
            Session::setFlash('danger', 'Erro ao atualizar registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro atualizado com sucesso');
        Response::redirect('/levels');
    }

    public static function delete($id)
    {
        $targetLevel = Level::get($id);

        if (!$targetLevel) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        $deleted = Level::delete($id);

        if (!$deleted) {
            Session::setFlash('danger', 'Erro ao excluir registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro excluído com sucesso');
        Response::redirect('/levels');
    }
}
