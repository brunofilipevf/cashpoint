<?php

namespace App\Controllers;

class ScoreController
{
    public function __construct(
        private \App\Models\Auth $auth,
        private \App\Models\Customer $customer,
        private \App\Models\Score $score,
        private \Core\Database $database,
        private \Core\Email $email,
        private \Core\Request $request,
        private \Core\Response $response,
        private \Core\Session $session,
        private \Core\Validator $validator,
        private \Core\ZAPI $zapi
    ) {}

    public function index()
    {
        $this->response->view('score/index', [
            'scores' => $this->score->all()
        ]);
    }

    public function show($scoreId)
    {
        $scoreData = $this->score->find($scoreId);

        if (!$scoreData) {
            $this->response->abort(404);
        }

        $this->response->view('score/show', [
            'score' => $scoreData
        ]);
    }

    public function add()
    {
        $this->response->view('score/add');
    }

    public function insert()
    {
        // -------------------------------------------------------------------
        // Obtém os dados do formulário
        // -------------------------------------------------------------------

        $requestData = [
            'username' => $this->request->post('username'),
            'password' => $this->request->post('password'),
            'cpf' => $this->request->post('cpf'),
            'points' => $this->request->post('points')
        ];

        // -------------------------------------------------------------------
        // Valida os campos obrigatórios
        // -------------------------------------------------------------------

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

        // -------------------------------------------------------------------
        // Autentica o usuário autorizador da pontuação
        // -------------------------------------------------------------------

        $authorizingUserId = $this->auth->login($requestData['username'], $requestData['password']);

        if (!$authorizingUserId) {
            $this->session->setFlash('danger', 'Credenciais inválidas ou usuário inativo');
            $this->response->redirect('same_uri');
        }

        // -------------------------------------------------------------------
        // Inicia transação para garantir consistência
        // -------------------------------------------------------------------

        $this->database->beginTransaction();

        // -------------------------------------------------------------------
        // Busca cliente com lock exclusivo
        // -------------------------------------------------------------------

        $customerData = $this->customer->findByCpfForUpdate($requestData['cpf']);

        if ($customerData['is_active'] !== 1) {
            $this->database->rollBack();
            $this->session->setFlash('danger', 'Cliente inativo');
            $this->response->redirect('same_uri');
        }

        // -------------------------------------------------------------------
        // Verifica limite diário de pontuações do cliente
        // -------------------------------------------------------------------

        $dailyCount = $this->score->countDailyByCustomer($customerData['id']);

        if ($dailyCount >= MAX_DAILY_LIMIT_POINTS_PER_CUSTOMER) {
            $this->database->rollBack();
            $this->session->setFlash('danger', 'Limite máximo de pontuação diaria por cliente atingido');
            $this->response->redirect('same_uri');
        }

        // -------------------------------------------------------------------
        // Prepara e insere a pontuação, confirmando a transação
        // -------------------------------------------------------------------

        $dataToBeSaved = [
            'transaction_code' => bin2hex(random_bytes(8)),
            'customer_id' => $customerData['id'],
            'base_points' => $requestData['points'],
            'final_points' => $requestData['points'],
            'user_id' => $authorizingUserId
        ];

        $this->score->insert($dataToBeSaved);
        $this->database->commit();

        // -------------------------------------------------------------------
        // Monta o corpo da notificação
        // -------------------------------------------------------------------

        if (!$customerData['fullname']) {
            $customerData['fullname'] = 'Cliente';
        }

        $currentBalance = $this->score->findBalanceFromCustomer($customerData['id'])['balance'];
        $transactionCode = implode('-', str_split($dataToBeSaved['transaction_code'], 4));

        $body  = "Olá, {$customerData['fullname']}\n\n";
        $body .= "Você acaba de receber {$dataToBeSaved['final_points']} pontos em sua conta!\n";
        $body .= "Seu saldo atual é de {$currentBalance} pontos\n";
        $body .= "Código de transação: {$transactionCode}\n\n";
        $body .= "Agradecemos a preferência!";

        // -------------------------------------------------------------------
        // Envia notificação por e-mail
        // -------------------------------------------------------------------

        if ($customerData['email']) {
            $this->email->send([
                'to' => $customerData['email'],
                'subject' => 'Pontos Creditados - ' . APP_NAME,
                'body' => $body
            ]);
        }

        // -------------------------------------------------------------------
        // Envia notificação por WhatsApp
        // -------------------------------------------------------------------

        if ($customerData['phone']) {
            $this->zapi->send([
                'phone' => $customerData['phone'],
                'message' => $body
            ]);
        }

        // -------------------------------------------------------------------
        // Redireciona com mensagem de sucesso
        // -------------------------------------------------------------------

        $this->session->setFlash('success', 'Pontuação registrada com sucesso');
        $this->response->redirect('/scores');
    }
}
