<?php

namespace App\Controllers;

use App\Models\Award;
use App\Models\Customer;
use App\Models\Redemption;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class RedemptionController
{
    public function __construct(
        private Award $award,
        private Customer $customer,
        private Redemption $redemption,
        private Response $response,
        private Request $request,
        private Session $session,
        private Validator $validator
    ) { }

    public function index()
    {
        $redemptions = $this->redemption->all();
        return $this->response->render('redemption/index', ['redemptions' => $redemptions]);
    }

    public function add()
    {
        $awards = $this->award->all();
        return $this->response->render('redemption/add', ['awards' => $awards]);
    }

    public function insert()
    {
        $data = [
            'cpf' => $this->request->input('cpf'),
            'award_id' => $this->request->input('award_id')
        ];

        $rules = [
            'cpf' => 'required|document|exist:customer,cpf',
            'award_id' => 'required|integer|exist:award,id'
        ];

        $labels = [
            'cpf' => 'CPF/CNPJ',
            'award_id' => 'prêmio'
        ];

        $errors = $this->validator->fields($data, $rules, $labels);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            return $this->response->previous();
        }

        # Obtém os dados do cliente informado
        $targetCustomer = $this->customer->getByCpf($data['cpf']);

        # Verifica se o cliente está ativo
        if ($targetCustomer['is_active'] !== 1) {
            $this->session->setFlash('danger', 'Cliente está inativo');
            return $this->response->previous();
        }

        # Obtém os dados do prêmio informado
        $targetAward = $this->award->get($data['award_id']);

        # Verifica se o prêmio está ativo
        if ($targetAward['is_active'] !== 1) {
            $this->session->setFlash('danger', 'Prêmio está inativo');
            return $this->response->previous();
        }

        $now = date('Y-m-d H:i:s');

        # Verifica se o prêmio está dentro de periodo de validade
        if ($now < $targetAward['start_date'] || $now > $targetAward['end_date']) {
            $this->session->setFlash('danger', 'Este prêmio está fora do período de validade');
            return $this->response->previous();
        }

        # Obtém o total de vezes que o prêmio foi resgatado
        $totalRedemptions = $this->redemption->countByAward($targetAward['id']);

        # Verifica se o limite total de resgates do prêmio foi atingido
        if ($totalRedemptions >= $targetAward['max_redemptions_total']) {
            $this->session->setFlash('danger', 'O limite total de resgates para este prêmio foi alcançado');
            return $this->response->previous();
        }

        # Obtém o total de vezes que o cliente resgatou o prêmio
        $customerRedemptions = $this->redemption->countByAwardAndCustomer($targetAward['id'], $targetCustomer['id']);

        # Verifica se o cliente atingiu o limite individual de resgates deste prêmio
        if ($customerRedemptions >= $targetAward['max_redemptions_per_customer']) {
            $this->session->setFlash('danger', 'Cliente atingiu o limite máximo de resgates para este prêmio');
            return $this->response->previous();
        }

        # Obtém o saldo de pontos do cliente
        $customerBalance = $this->customer->getBalance($targetCustomer['id']);

        # Verifica se o cliente possui saldo suficiente
        if ($customerBalance < $targetAward['required_points']) {
            $this->session->setFlash('danger', 'Cliente não possui saldo suficiente para este resgate');
            return $this->response->previous();
        }

        # Obtém o ID do usuário autenticado
        $authId = $this->session->get('auth.id');

        $awardData = [
            'customer_id' => $targetCustomer['id'],
            'award_id' => $targetAward['id'],
            'product_id' => $targetAward['product_id'],
            'user_id' => $authId,
            'points_used' => $targetAward['required_points'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $inserted = $this->redemption->insert($awardData);

        if (!$inserted) {
            $this->session->setFlash('danger', 'Erro ao adicionar registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro adicionado com sucesso');
        return $this->response->redirect('/redemptions');
    }
}
