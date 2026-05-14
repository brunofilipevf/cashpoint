<?php

namespace App\Controllers;

use App\Models\Auth;
use App\Models\Customer;
use App\Models\Score;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class ScoreController
{
    public static function index()
    {
        $scores = Score::all();
        return Response::view('score/index', ['scores' => $scores]);
    }

    public static function add()
    {
        return Response::view('score/add');
    }

    public static function insert()
    {
        $data = [
            'username' => Request::input('username'),
            'password' => Request::input('password'),
            'cpf' => Request::input('cpf'),
            'points' => Request::input('points')
        ];

        $rules = [
            'username' => 'required|string',
            'password' => 'required|string',
            'cpf' => 'required|document|exist:customer,cpf',
            'points' => 'required|numeric|min:0.01'
        ];

        $labels = [
            'username' => 'nome de usuário',
            'password' => 'senha',
            'cpf' => 'CPF/CNPJ',
            'points' => 'pontos'
        ];

        $errors = Validator::fields($data, $rules, $labels);

        if ($errors) {
            Session::setFlash('danger', $errors);
            return Response::previous();
        }

        $authId = Auth::attempt($data);

        if (!$authId) {
            Session::setFlash('danger', 'Credenciais inválidas ou usuário inativo');
            return Response::previous();
        }

        $targetCustomer = Customer::getByCpf($data['cpf']);

        if (!$targetCustomer || (int) $targetCustomer['is_active'] !== 1) {
            Session::setFlash('danger', 'Cliente não encontrado ou inativo');
            return Response::previous();
        }

        $newData = [
            'customer_id' => $targetCustomer['id'],
            'user_id' => $authId,
            'base_points' => $data['points'],
            'multiplier_factor' => 1,
            'final_points' => $data['points'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $inserted = Score::insert($newData);

        if (!$inserted) {
            Session::setFlash('danger', 'Erro ao adicionar registro');
            return Response::previous();
        }

        Session::setFlash('success', 'Registro adicionado com sucesso');
        return Response::redirect('/scores');
    }
}
