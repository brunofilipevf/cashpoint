<?php

namespace App\Controllers;

use App\Models\{Auth, Customer, Score};
use Core\{Database, Request, Response, Session, Validator};

class ScoreController
{
    public static function index()
    {
        Response::view('score/index', [
            'scores' => Score::all()
        ]);
    }

    public static function add()
    {
        Response::view('score/add');
    }

    public static function insert()
    {
        $requestData = [
            'username' => Request::input('username'),
            'password' => Request::input('password'),
            'cpf' => Request::input('cpf'),
            'points' => Request::input('points')
        ];

        $errors = Validator::fields($requestData, [
            'username' => 'required|string',
            'password' => 'required|string',
            'cpf' => 'required|document|exist:customer,cpf',
            'points' => 'required|numeric|min:0.01|max:' . MAX_VALUE_LIMIT_MANUAL_POINTS
        ], [
            'username' => 'nome de usuário',
            'password' => 'senha',
            'cpf' => 'CPF/CNPJ',
            'points' => 'pontos'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::redirect('same_uri');
        }

        $authorizingUserId = Auth::login($requestData['username'], $requestData['password']);

        if (!$authorizingUserId) {
            Session::setFlash('danger', 'Credenciais inválidas ou usuário inativo');
            Response::redirect('same_uri');
        }

        Database::beginTransaction();

        try {

            $customerData = Customer::findByCpfForUpdate($requestData['cpf']);

            if ($customerData['is_active'] !== 1) {
                Database::rollBack();
                Session::setFlash('danger', 'Cliente inativo');
                Response::redirect('same_uri');
            }

            $dailyCount = Score::countDailyByCustomer($customerData['id']);

            if ($dailyCount >= MAX_DAILY_LIMIT_POINTS_PER_CUSTOMER) {
                Database::rollBack();
                Session::setFlash('danger', 'Limite máximo de pontuação diaria por cliente atingido');
                Response::redirect('same_uri');
            }

            $dataToBeSaved = [
                'transaction_code' => bin2hex(random_bytes(16)),
                'customer_id' => $customerData['id'],
                'base_points' => $requestData['points'],
                'final_points' => $requestData['points'],
                'user_id' => $authorizingUserId
            ];

            Score::insert($dataToBeSaved);
            Database::commit();

        } catch (\Exception) {
            Database::rollBack();
            Session::setFlash('danger', 'Erro ao registrar pontuação');
        }

        Session::setFlash('success', 'Pontuação registrada com sucesso');
        Response::redirect('/scores');
    }
}
