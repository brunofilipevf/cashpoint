<?php

namespace App\Controllers;

use App\Models\Award;
use App\Models\Redemption;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class RedemptionController
{
    public static function index()
    {
        $redemptions = Redemption::all();
        return Response::view('redemption/index', ['redemptions' => $redemptions]);
    }

    public static function add()
    {
        $awards = Award::all();
        return Response::view('redemption/add', ['awards' => $awards]);
    }

    public static function insert()
    {
        $data = [
            'cpf' => Request::input('cpf'),
            'award_id' => Request::input('award_id')
        ];

        $rules = [
            'cpf' => 'required|document|exist:customer,cpf',
            'award_id' => 'required|integer|exist:award,id'
        ];

        $labels = [
            'cpf' => 'CPF/CNPJ',
            'award_id' => 'prêmio'
        ];

        $errors = Validator::fields($data, $rules, $labels);

        if ($errors) {
            Session::setFlash('danger', $errors);
            return Response::previous();
        }

        $authId = Session::get('auth.id');
        $targetCustomer = Customer::getByCpf($data['cpf']);

        if (!$targetCustomer || (int) $targetCustomer['is_active'] !== 1) {
            Session::setFlash('danger', 'Cliente não encontrado ou inativo');
            return Response::previous();
        }

        $targetAward = Award::get($data['award_id']);

        if (!$targetAward || (int) $targetAward['is_active'] !== 1) {
            Session::setFlash('danger', 'Prêmio não encontrado ou inativo');
            return Response::previous();
        }

        # <verificar_periodo_de_validade_do_award>

        # <verificar_max_redemptions_total>

        # <verificar_max_redemptions_per_customer>

        $newData = [
            'customer_id' => $targetCustomer['id'],
            'award_id' => $data['id'],
            'product_id' => $targetAward['product_id'],
            'user_id' => $authId,
            'points_used' => $targetAward['required_points'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $inserted = Redemption::insert($newData);

        if (!$inserted) {
            Session::setFlash('danger', 'Erro ao adicionar registro');
            return Response::previous();
        }

        Session::setFlash('success', 'Registro adicionado com sucesso');
        return Response::redirect('/redemptions');
    }
}
