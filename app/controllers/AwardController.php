<?php

namespace App\Controllers;

use App\Models\Award;
use App\Models\Product;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class AwardController
{
    public static function index()
    {
        $awards = Award::all();
        return Response::view('award/index', ['awards' => $awards]);
    }

    public static function add()
    {
        $products = Product::all();
        return Response::view('award/add', ['products' => $products]);
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

        $rules = [
            'name' => 'required|string|min:2|max:60',
            'product_id' => 'required|integer|exist:product,id',
            'required_points' => 'required|numeric|min:0.01',
            'max_redemptions_total' => 'required|integer|min:1',
            'max_redemptions_per_customer' => 'required|integer|min:1|before_or_equal:max_redemptions_total',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ];

        $labels = [
            'name' => 'nome',
            'product_id' => 'produto',
            'required_points' => 'pontos necessários',
            'max_redemptions_total' => 'limite total de resgates',
            'max_redemptions_per_customer' => 'limite de resgates por cliente',
            'start_date' => 'data de início',
            'end_date' => 'data de término'
        ];

        $errors = Validator::fields($data, $rules, $labels);

        if ($errors) {
            Session::setFlash('danger', $errors);
            return Response::previous();
        }

        $inserted = Award::insert($data);

        if (!$inserted) {
            Session::setFlash('danger', 'Erro ao adicionar registro');
            return Response::previous();
        }

        Session::setFlash('success', 'Registro adicionado com sucesso');
        return Response::redirect('/awards');
    }

    public static function edit($id)
    {
        $targetAward = Award::get($id);

        if (!$targetAward) {
            Session::setFlash('danger', 'Registro não encontrado');
            return Response::previous();
        }

        $products = Product::all();
        return Response::view('award/edit', [
            'award' => $targetAward,
            'products' => $products
        ]);
    }

    public static function update($id)
    {
        $targetAward = Award::get($id);

        if (!$targetAward) {
            Session::setFlash('danger', 'Registro não encontrado');
            return Response::previous();
        }

        $data = [
            'name' => Request::input('name'),
            'product_id' => Request::input('product_id'),
            'required_points' => Request::input('required_points'),
            'max_redemptions_total' => Request::input('max_redemptions_total'),
            'max_redemptions_per_customer' => Request::input('max_redemptions_per_customer'),
            'start_date' => Request::input('start_date'),
            'end_date' => Request::input('end_date'),
            'is_active' => Request::input('is_active'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $rules = [
            'name' => 'required|string|min:2|max:60',
            'product_id' => 'required|integer|exist:product,id',
            'required_points' => 'required|numeric|min:0.01',
            'max_redemptions_total' => 'required|integer|min:1',
            'max_redemptions_per_customer' => 'required|integer|min:1|before_or_equal:max_redemptions_total',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'required|in:0,1'
        ];

        $labels = [
            'name' => 'nome',
            'product_id' => 'produto',
            'required_points' => 'pontos necessários',
            'max_redemptions_total' => 'limite total de resgates',
            'max_redemptions_per_customer' => 'limite de resgates por cliente',
            'start_date' => 'data de início',
            'end_date' => 'data de término',
            'is_active' => 'status'
        ];

        $errors = Validator::fields($data, $rules, $labels);

        if ($errors) {
            Session::setFlash('danger', $errors);
            return Response::previous();
        }

        $updated = Award::update($data, $id);

        if (!$updated) {
            Session::setFlash('danger', 'Erro ao atualizar registro');
            return Response::previous();
        }

        Session::setFlash('success', 'Registro atualizado com sucesso');
        return Response::redirect('/awards');
    }

    public static function delete($id)
    {
        $targetAward = Award::get($id);

        if (!$targetAward) {
            Session::setFlash('danger', 'Registro não encontrado');
            return Response::previous();
        }

        $deleted = Award::delete($id);

        if (!$deleted) {
            Session::setFlash('danger', 'Erro ao excluir registro');
            return Response::previous();
        }

        Session::setFlash('success', 'Registro excluído com sucesso');
        return Response::redirect('/awards');
    }
}
