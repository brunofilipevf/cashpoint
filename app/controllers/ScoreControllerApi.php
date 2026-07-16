<?php

namespace App\Controllers;

class ScoreControllerApi
{
    public function __construct(
        private \App\Models\Company $company,
        private \App\Models\Customer $customer,
        private \App\Models\Group $group,
        private \App\Models\Score $score,
        private \Core\Database $database,
        private \Core\Request $request,
        private \Core\Response $response,
        private \Core\Validator $validator
    ) {}

    public function add()
    {
        $fillables = [
            'cpf' => 'CPF/CNPJ'
        ];

        $this->response->json($fillables);
    }

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
                'amount' => 'required|numeric:10,3'
            ], [
                'company_cpf' => 'CPF/CNPJ da empresa',
                'customer_cpf' => 'CPF/CNPJ do cliente',
                'supply_code' => 'código de barras',
                'amount' => 'quantidade'
            ]);

            if ($errors) {
                $this->database->rollBack();
                $this->response->json(['error', $errors[0]]);
            }

            $scoreData = $this->score->findBySupplyCode($requestData['supply_code']);

            if ($scoreData) {
                $this->response->json(['error', 'Este abastecimento já foi pontuado']);
            }

            $companyData = $this->company->findByCpfForUpdate($requestData['company_cpf']);

            if ($companyData['is_active'] !== 1) {
                $this->database->rollBack();
                $this->response->json(['error', 'Empresa inativa']);
            }

            $customerData = $this->customer->findByCpfForUpdate($requestData['customer_cpf']);

            if ($customerData['is_active'] !== 1) {
                $this->database->rollBack();
                $this->response->json(['error', 'Cliente inativo']);
            }

            $groupData = $this->group->findForUpdate($customerData['group_id']);

            if (!$groupData) {
                $groupData = ['multiplier_factor' => 1];
            } elseif ($groupData['is_active'] !== 1) {
                $this->database->rollBack();
                $this->response->json(['error', 'Grupo do cliente inativo']);
            }

            $dailyCount = $this->score->countDailyByCustomer($customerData['id']);

            if ($dailyCount >= MAX_DAILY_LIMIT_POINTS_PER_CUSTOMER) {
                $this->database->rollBack();
                $this->response->json(['error', 'Limite máximo de pontuação diaria por cliente atingido']);
            }

            $requestArray = $this->request->json();

            unset($requestArray['cpf'], $requestArray['numero']);

            $dataToBeSaved = [
                'transaction_code' => bin2hex(random_bytes(32)),
                'customer_id' => $customerData['id'],
                'base_points' => number_format((float) $requestData['amount'], 2, '.', ''),
                'multiplier_factor' => $groupData['multiplier_factor'],
                'final_points' => number_format((float) $requestData['amount'] * (float) $groupData['multiplier_factor'], 2, '.', ''),
                'company_id' => $companyData['id'],
                'supply_code' => $requestData['supply_code'],
                'supply_json' => json_encode($requestArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ];

            $this->score->insert($dataToBeSaved);
            $this->database->commit();
            $this->response->json(['success', 'Pontuação registrada com sucesso']);

        } catch (\Exception $e) {
            $this->database->rollBack();
            $this->response->json(['error', 'Erro ao registrar pontuação: '.(string) $e]);
        }
    }
}
