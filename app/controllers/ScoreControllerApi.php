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
        private \Core\Email $email,
        private \Core\Request $request,
        private \Core\Response $response,
        private \Core\Validator $validator
    ) {}

    public function add()
    {
        $fillables = [
            'cliente' => 'CPF/CNPJ'
        ];

        $this->response->json($fillables);
    }

    public function insert()
    {
        $this->database->beginTransaction();

        try {

            $requestData = [
                'company_cpf' => $this->request->json()['empresa'],
                'customer_cpf' => $this->request->json()['cliente'],
                'supply_code' => $this->request->json()['codigo'],
                'nozzle' => $this->request->json()['bico'],
                'product_name' => $this->request->json()['produto_nome'],
                'amount' => $this->request->json()['quantidade'],
                'unit_price' => $this->request->json()['preco_unit'],
                'total_value' => $this->request->json()['valor_total'],
                'date_hour' => $this->request->json()['hora']
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

            $supplyJsonData = json_encode([
                'codigo' => $requestData['supply_code'],
                'bico' => $requestData['nozzle'],
                'produto' => $requestData['product_name'],
                'quantidade' => $requestData['amount'],
                'preco_unit' => $requestData['unit_price'],
                'valor_total' => $requestData['total_value'],
                'data_hora' => $requestData['date_hour']
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $dataToBeSaved = [
                'transaction_code' => date('Ymd') . substr(md5(uniqid(mt_rand(), true)), 0, 8),
                'customer_id' => $customerData['id'],
                'base_points' => number_format((float) $requestData['amount'], 2, '.', ''),
                'multiplier_factor' => $groupData['multiplier_factor'],
                'final_points' => number_format((float) $requestData['amount'] * (float) $groupData['multiplier_factor'], 2, '.', ''),
                'is_manual' => 0,
                'company_id' => $companyData['id'],
                'supply_code' => $requestData['supply_code'],
                'supply_json' => $supplyJsonData
            ];

            $this->score->insert($dataToBeSaved);
            $this->database->commit();

            if ($customerData['email']) {
                $body = "Olá, %s\n\nVocê acaba de receber %s pontos em sua conta!\nCódigo de transação: %s\n\nAgradecemos a preferência!";

                if (!$customerData['fullname']) {
                    $customerData['fullname'] = 'Cliente';
                }

                $this->email->send([
                    'to' => $customerData['email'],
                    'subject' => 'Pontos Creditados - ' . APP_NAME,
                    'body' => sprintf(
                        $body,
                        $customerData['fullname'],
                        $dataToBeSaved['final_points'],
                        $dataToBeSaved['transaction_code']
                    )
                ]);
            }

            $receipt = sprintf(
                "================================\n" .
                "     COMPROVANTE DE PONTUAÇÃO\n" .
                "================================\n\n" .
                "Cliente: %s\n" .
                "Produto: %s\n" .
                "Qtd: %d L\n" .
                "Pontos: %s\n" .
                "Saldo atual: %s\n" .
                "Data: %s\n" .
                "Transação: %s\n\n" .
                "================================\n" .
                "    Obrigado pela Compra!\n" .
                "================================",
                $customerData['fullname'],
                $requestData['product_name'],
                $requestData['amount'],
                $dataToBeSaved['final_points'],
                $this->score->findBalanceFromCustomer($customerData['id'])['balance'],
                date('d/m/Y H:i:s'),
                implode('-', str_split($dataToBeSaved['transaction_code'], 4))
            );

            $this->response->json(['success', 'Pontuação registrada com sucesso', $receipt]);

        } catch (\Throwable $e) {

            error_log('[ScoreAPI] Erro ao registrar pontuação: ' . (string) $e);
            $this->database->rollBack();
            $this->response->json(['error', 'Erro ao registrar pontuação']);

        }
    }
}
