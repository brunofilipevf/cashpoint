<?php

namespace App\Controllers;

class RedemptionController
{
    public function __construct(
        private \App\Models\Auth $auth,
        private \App\Models\Award $award,
        private \App\Models\Customer $customer,
        private \App\Models\Redemption $redemption,
        private \App\Models\Score $score,
        private \Core\Database $database,
        private \Core\Email $email,
        private \Core\Request $request,
        private \Core\Response $response,
        private \Core\Session $session,
        private \Core\Validator $validator
    ) {}

    public function index()
    {
        $this->response->view('redemption/index', [
            'redemptions' => $this->redemption->all()
        ]);
    }

    public function show($redemptionId)
    {
        $redemptionData = $this->redemption->find($redemptionId);

        if (!$redemptionData) {
            $this->response->abort(404);
        }

        $this->response->view('redemption/show', [
            'redemption' => $redemptionData
        ]);
    }

    public function add()
    {
        $this->response->view('redemption/add', [
            'awards' => $this->award->allAvailable()
        ]);
    }

    public function insert()
    {
        $authUserData = $this->auth->stored();

        $requestData = [
            'cpf' => $this->request->post('cpf'),
            'award_id' => $this->request->post('award_id')
        ];

        $errors = $this->validator->fields($requestData, [
            'cpf' => 'required|document|exist:customer,cpf',
            'award_id' => 'required|integer|exist:award,id'
        ], [
            'cpf' => 'CPF/CNPJ',
            'award_id' => 'premiação'
        ]);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            $this->response->redirect('same_uri');
        }

        $this->database->beginTransaction();

        $customerData = $this->customer->findByCpfForUpdate($requestData['cpf']);

        if ($customerData['is_active'] !== 1) {
            $this->database->rollBack();
            $this->session->setFlash('danger', 'Cliente inativo');
            $this->response->redirect('same_uri');
        }

        $awardData = $this->award->findForUpdate($requestData['award_id']);

        if ($awardData['is_active'] !== 1) {
            $this->database->rollBack();
            $this->session->setFlash('danger', 'Premiação inativa');
            $this->response->redirect('same_uri');
        }

        $now = date('Y-m-d H:i:s');

        if ($now < $awardData['start_date'] || $now > $awardData['end_date']) {
            $this->database->rollBack();
            $this->session->setFlash('danger', 'Premiação fora do período de validade');
            $this->response->redirect('same_uri');
        }

        $totalRedemptions = $this->redemption->countByAward($awardData['id']);

        if ($totalRedemptions >= $awardData['max_redemption_total']) {
            $this->database->rollBack();
            $this->session->setFlash('danger', 'Limite total de resgate para esta premiação atingido');
            $this->response->redirect('same_uri');
        }

        $customerRedemptions = $this->redemption->countByAwardAndCustomer($awardData['id'], $customerData['id']);

        if ($customerRedemptions >= $awardData['max_redemption_per_customer']) {
            $this->database->rollBack();
            $this->session->setFlash('danger', 'Limite de resgate por cliente para este premiação atingido');
            $this->response->redirect('same_uri');
        }

        $balanceData = $this->score->findBalanceFromCustomer($customerData['id']);

        if ($balanceData['balance'] < $awardData['required_points']) {
            $this->database->rollBack();
            $this->session->setFlash('danger', 'Cliente não possui saldo suficiente para resgatar esta premiação');
            $this->response->redirect('same_uri');
        }

        $dataToBeSaved = [
            'transaction_code' => bin2hex(random_bytes(32)),
            'customer_id' => $customerData['id'],
            'award_id' => $awardData['id'],
            'product_id' => $awardData['product_id'],
            'user_id' => $authUserData['id'],
            'points_used' => $awardData['required_points']
        ];

        $insertedId = $this->redemption->insert($dataToBeSaved);
        $this->database->commit();

        if ($customerData['email']) {
            $body = "Olá, %s\n\nResgate realizado com sucesso!\nPremiação: %s\nPontos utilizados: %s\nCódigo de transação: %s\n\nAproveite!";

            if (!$customerData['fullname']) {
                $customerData['fullname'] = 'Cliente';
            }

            $this->email->send([
                'to' => $customerData['email'],
                'subject' => 'Resgate de Premiação - ' . APP_NAME,
                'body' => sprintf(
                    $body,
                    $customerData['fullname'],
                    $awardData['name'],
                    $dataToBeSaved['points_used'],
                    $dataToBeSaved['transaction_code']
                )
            ]);
        }

        $this->session->setFlash('success', 'Resgate registrado com sucesso');
        $this->response->redirect('/redemptions/show/' . $insertedId);
    }
}
