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
    public function __construct(
        private Auth $auth,
        private Customer $customer,
        private Score $score,
        private Response $response,
        private Request $request,
        private Session $session,
        private Validator $validator
    ) { }

    public function index()
    {
        $scores = $this->score->all();
        return $this->response->render('score/index', ['scores' => $scores]);
    }

    public function add()
    {
        return $this->response->render('score/add');
    }

    public function insert()
    {
        $data = [
            'username' => $this->request->input('username'),
            'password' => $this->request->input('password'),
            'cpf' => $this->request->input('cpf'),
            'points' => $this->request->input('points')
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

        $errors = $this->validator->fields($data, $rules, $labels);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            return $this->response->previous();
        }

        $authData = [
            'username' => $data['username'],
            'password' => $data['password']
        ];

        $authId = $this->auth->attempt($authData);

        if (!$authId) {
            $this->session->setFlash('danger', 'Credenciais inválidas ou usuário inativo');
            return $this->response->previous();
        }

        # Obtém os dados do cliente informado
        $targetCustomer = $this->customer->getByCpf($data['cpf']);

        # Verifica se o cliente está ativo
        if ($targetCustomer['is_active'] !== 1) {
            $this->session->setFlash('danger', 'Cliente está inativo');
            return $this->response->previous();
        }

        $scoreData = [
            'customer_id' => $targetCustomer['id'],
            'user_id' => $authId,
            'base_points' => $data['points'],
            'multiplier_factor' => 1,
            'final_points' => $data['points'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $inserted = $this->score->insert($scoreData);

        if (!$inserted) {
            $this->session->setFlash('danger', 'Erro ao adicionar registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro adicionado com sucesso');
        return $this->response->redirect('/scores');
    }
}
