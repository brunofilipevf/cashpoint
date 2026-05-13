<?php

namespace App\Controllers;

use App\Models\Award;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class AwardController
{
    public static function index()
    {
        $awards = Award::all();
        Response::view('award/index', ['awards' => $awards]);
    }

    public static function add()
    {
        Response::view('award/add');
    }

    public static function insert()
    {
        $data = [
            'name' => Request::input('name'),
            'product_id' => Request::input('product_id'),
            'required_points' => Request::input('required_points'),
            'max_redemptions_total' => Request::input('max_redemptions_total'),
            'max_redemptions_per_customer' => Request::input('max_redemptions_per_customer'),
            'start_date' => Request::input('start_date'),
            'end_date' => Request::input('end_date'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $errors = Validator::fields($data, [
            'name' => 'required|string|min:2|max:60',
            'product_id' => 'required|integer|exist:product,id',
            'required_points' => 'required|numeric|min:0.01',
            'max_redemptions_total' => 'required|integer|min:1',
            'max_redemptions_per_customer' => 'required|integer|min:1|before_or_equal:max_redemptions_total',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ], [
            'name' => 'nome',
            'product_id' => 'produto',
            'required_points' => 'pontos necessários',
            'max_redemptions_total' => 'limite total de resgates',
            'max_redemptions_per_customer' => 'limite de resgates por cliente',
            'start_date' => 'data de início',
            'end_date' => 'data de término'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::previous();
        }

        $inserted = Award::insert($data);

        if (!$inserted) {
            Session::setFlash('danger', 'Erro ao adicionar registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro adicionado com sucesso');
        Response::redirect('/awards');
    }

    public static function edit($id)
    {
        $targetAward = Award::get($id);

        if (!$targetAward) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        Response::view('award/edit', ['award' => $targetAward]);
    }

    public static function update($id)
    {
        $targetAward = Award::get($id);

        if (!$targetAward) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        $data = [
            'name' => Request::input('name'),
            'product_id' => Request::input('product_id'),
            'required_points' => Request::input('required_points'),
            'max_redemptions_total' => Request::input('max_redemptions_total'),
            'max_redemptions_per_customer' => Request::input('max_redemptions_per_customer'),
            'start_date' => Request::input('start_date'),
            'end_date' => Request::input('end_date'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $errors = Validator::fields($data, [
            'name' => 'required|string|min:2|max:60',
            'product_id' => 'required|integer|exist:product,id',
            'required_points' => 'required|numeric|min:0.01',
            'max_redemptions_total' => 'required|integer|min:1',
            'max_redemptions_per_customer' => 'required|integer|min:1|before_or_equal:max_redemptions_total',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'required|in:0,1'
        ], [
            'name' => 'nome',
            'product_id' => 'produto',
            'required_points' => 'pontos necessários',
            'max_redemptions_total' => 'limite total de resgates',
            'max_redemptions_per_customer' => 'limite de resgates por cliente',
            'start_date' => 'data de início',
            'end_date' => 'data de término',
            'is_active' => 'status'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::previous();
        }

        $updated = Award::update($data, $id);

        if (!$updated) {
            Session::setFlash('danger', 'Erro ao atualizar registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro atualizado com sucesso');
        Response::redirect('/awards');
    }

    public static function delete($id)
    {
        $targetAward = Award::get($id);

        if (!$targetAward) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        $deleted = Award::delete($id);

        if (!$deleted) {
            Session::setFlash('danger', 'Erro ao excluir registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro excluído com sucesso');
        Response::redirect('/awards');
    }
}
