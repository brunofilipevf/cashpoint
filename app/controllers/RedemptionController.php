<?php

namespace App\Controllers;

use App\Models\{Auth, Award, Redemption};
use Core\{Database, Email, Request, Response, Session, Validator};

class RedemptionController
{
    public static function index()
    {
        Response::view('redemption/index', [
            'redemptions' => Redemption::all()
        ]);
    }

    public static function add()
    {
        Response::view('redemption/add', [
            'awards' => Award::allAvailable()
        ]);
    }

    public static function insert()
    {
        $authUserData = Auth::stored();

        $requestData = [
            'cpf' => Request::input('cpf'),
            'award_id' => Request::input('award_id')
        ];

        $errors = Validator::fields($requestData, [
            'cpf' => 'required|document|exist:customer,cpf',
            'award_id' => 'required|integer|exist:award,id'
        ], [
            'cpf' => 'CPF/CNPJ',
            'award_id' => 'premiação'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::redirect('same_uri');
        }

        // -------------------------------------------------------------------
        // Inicia transação para garantir consistência
        // -------------------------------------------------------------------

        Database::beginTransaction();

        try {
            $customerData = Customer::getByCpfForUpdate($requestData['cpf']);

            if ($customerData['is_active'] !== 1) {
                Database::rollBack();
                Session::setFlash('danger', 'Cliente inativo');
                Response::redirect('same_uri');
            }

            $awardData = Award::getForUpdate($requestData['award_id']);

            if ($awardData['is_active'] !== 1) {
                Database::rollBack();
                Session::setFlash('danger', 'Premiação inativa');
                Response::redirect('same_uri');
            }

            $now = date('Y-m-d H:i:s');

            if ($now < $awardData['start_date'] || $now > $awardData['end_date']) {
                Database::rollBack();
                Session::setFlash('danger', 'Premiação fora do período de validade');
                Response::redirect('same_uri');
            }

            $totalRedemptions = Redemption::countByAward($awardData['id']);

            if ($totalRedemptions >= $awardData['max_redemption_total']) {
                Database::rollBack();
                Session::setFlash('danger', 'Limite total de resgate para esta premiação atingido');
                Response::redirect('same_uri');
            }

            $customerRedemptions = Redemption::countByAwardAndCustomer($awardData['id'], $customerData['id']);

            if ($customerRedemptions >= $awardData['max_redemption_per_customer']) {
                Database::rollBack();
                Session::setFlash('danger', 'Limite de resgate por cliente para este premiação atingido');
                Response::redirect('same_uri');
            }

            $balanceData = Score::getBalanceFromCustomer($customerData['id']);

            if ($balanceData['balance'] < $awardData['required_points']) {
                Database::rollBack();
                Session::setFlash('danger', 'Cliente não possui saldo suficiente para resgatar esta premiação');
                Response::redirect('same_uri');
            }

            $dataToBeSaved = [
                'transaction_code' => bin2hex(random_bytes(16)),
                'customer_id' => $customerData['id'],
                'award_id' => $awardData['id'],
                'product_id' => $awardData['product_id'],
                'user_id' => $authUserData['id'],
                'points_used' => $awardData['required_points']
            ];

            Redemption::insert($dataToBeSaved);
            Database::commit();

        } catch (\PDOException) {
            // ---------------------------------------------------------------
            // Reverte a transação em caso de erro
            // ---------------------------------------------------------------

            Database::rollBack();
            Response::json(['fail', 'Erro ao adicionar abastecimento'], 500);
        }

        if ($customerData['email']) {
            $subject = 'Resgate de Premiação - ' . APP_NAME;
            $customerName = 'Cliente';

            if ($customerData['fullname']) {
                $customerName = $customerData['fullname'];
            }

            Email::send($customerData['email'], $subject, sprintf(
                EMAIL_REWARD_REDEEMED,
                $customerName,
                $awardData['name'],
                $dataToBeSaved['points_used'],
                $dataToBeSaved['transaction_code']
            ));
        }

        Session::setFlash('success', 'Resgate registrado com sucesso');
        Response::redirect('/redemptions');
    }
}
