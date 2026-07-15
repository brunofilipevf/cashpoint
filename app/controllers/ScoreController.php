<?php

namespace App\Controllers;

class ScoreController
{
    public function __construct(
        private \App\Models\Auth $auth,
        private \App\Models\Customer $customer,
        private \App\Models\Score $score,
        private \Core\Database $database,
        private \Core\Request $request,
        private \Core\Response $response,
        private \Core\Session $session,
        private \Core\Validator $validator
    ) {}

    public function index()
    {
        $this->response->view('score/index', [
            'scores' => $this->score->all()
        ]);
    }

    public function add()
    {
        $this->response->view('score/add');
    }

    public function insert()
    {
        $requestData = [
            'username' => $this->request->post('username'),
            'password' => $this->request->post('password'),
            'cpf' => $this->request->post('cpf'),
            'points' => $this->request->post('points')
        ];

        $errors = $this->validator->fields($requestData, [
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
            $this->session->setFlash('danger', $errors);
            $this->response->redirect('same_uri');
        }

        $authorizingUserId = $this->auth->login($requestData['username'], $requestData['password']);

        if (!$authorizingUserId) {
            $this->session->setFlash('danger', 'Credenciais inválidas ou usuário inativo');
            $this->response->redirect('same_uri');
        }

        $this->database->beginTransaction();

        $customerData = $this->customer->findByCpfForUpdate($requestData['cpf']);

        if ($customerData['is_active'] !== 1) {
            $this->database->rollBack();
            $this->session->setFlash('danger', 'Cliente inativo');
            $this->response->redirect('same_uri');
        }

        $dailyCount = $this->score->countDailyByCustomer($customerData['id']);

        if ($dailyCount >= MAX_DAILY_LIMIT_POINTS_PER_CUSTOMER) {
            $this->database->rollBack();
            $this->session->setFlash('danger', 'Limite máximo de pontuação diaria por cliente atingido');
            $this->response->redirect('same_uri');
        }

        $dataToBeSaved = [
            'transaction_code' => bin2hex(random_bytes(32)),
            'customer_id' => $customerData['id'],
            'base_points' => $requestData['points'],
            'final_points' => $requestData['points'],
            'user_id' => $authorizingUserId
        ];

        $this->score->insert($dataToBeSaved);
        $this->database->commit();
        $this->session->setFlash('success', 'Pontuação registrada com sucesso');
        $this->response->redirect('/scores');
    }
}
