<?php

namespace App\Controllers;

class ScoreControllerApi
{
    public function __construct(
        private \App\Models\Company $company,
        private \App\Models\Customer $customer,
        private \App\Models\Score $score,
        private \Core\Database $database,
        private \Core\Request $request,
        private \Core\Response $response,
        private \Core\Validator $validator
    ) {}

    public function insert()
    {
        $this->database->beginTransaction();

        try {

            $requestData = [
                'company_cpf' => $this->request->json()['empresa'],
                'customer_cpf' => $this->request->json()['cpf'],
                'supply_code' => $this->request->json()['codigo'],
                'amount' => $this->request->json()['quantidade']
            ];

            $errors = $this->validator->fields($requestData, [
                'company_cpf' => 'required|document|exist:company,cpf',
                'customer_cpf' => 'required|document|exist:customer,cpf',
                'supply_code' => 'required|integer',
                'amount' => 'required|numeric'
            ], [
                'company_cpf' => 'CPF/CNPJ da empresa',
                'customer_cpf' => 'CPF/CNPJ do cliente',
                'supply_code' => 'código de barras',
                'amount' => 'quantidade'
            ]);

            if ($errors) {
                $this->database->rollBack();
                $this->response->json($errors);
            }

            $companyData = $this->company->findByCpfForUpdate($requestData['company_cpf']);

            if ($companyData['is_active'] !== 1) {
                $this->database->rollBack();
                $this->response->json('Empresa inativa');
            }

            $customerData = $this->customer->findByCpfForUpdate($requestData['customer_cpf']);

            if ($customerData['is_active'] !== 1) {
                $this->database->rollBack();
                $this->response->json('Cliente inativo');
            }

            $dailyCount = $this->score->countDailyByCustomer($customerData['id']);

            if ($dailyCount >= MAX_DAILY_LIMIT_POINTS_PER_CUSTOMER) {
                $this->database->rollBack();
                $this->response->json('Limite máximo de pontuação diaria por cliente atingido');
            }

            $dataToBeSaved = [
                'transaction_code' => bin2hex(random_bytes(32)),
                'customer_id' => $customerData['id'],
                'base_points' => $requestData['amount'],
                'final_points' => $requestData['amount'],
                'company_id' => $companyData['id'],
                'supply_code' => $requestData['supply_code']
            ]

            $this->score->insert($dataToBeSaved);
            $this->database->commit();
            $this->response->json('Pontuação registrada com sucesso');

        } catch (\Exception) {
            $this->database->rollBack();
            $this->response->json('Erro ao registrar pontuação');
        }
    }
}
