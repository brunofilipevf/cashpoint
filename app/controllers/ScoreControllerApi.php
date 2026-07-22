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
        private \Core\Validator $validator,
        private \Core\ZAPI $zapi
    ) {}

    public function add()
    {
        // -------------------------------------------------------------------
        // Retorna os campos esperados para pontuação
        // -------------------------------------------------------------------

        $fillables = [
            'cliente' => 'CPF/CNPJ'
        ];

        $this->response->json($fillables);
    }

    public function insert()
    {
        // -------------------------------------------------------------------
        // Inicia transação para garantir consistência
        // -------------------------------------------------------------------

        $this->database->beginTransaction();

        try {

            // -------------------------------------------------------------------
            // Obtém os dados do corpo da requisição
            // -------------------------------------------------------------------

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

            // -------------------------------------------------------------------
            // Valida os campos obrigatórios
            // -------------------------------------------------------------------

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

            // -------------------------------------------------------------------
            // Verifica se o abastecimento já foi pontuado
            // -------------------------------------------------------------------

            $scoreData = $this->score->findBySupplyCode($requestData['supply_code']);

            if ($scoreData) {
                $this->database->rollBack();
                $this->response->json(['error', 'Este abastecimento já foi pontuado']);
            }

            // -------------------------------------------------------------------
            // Busca empresa com lock exclusivo
            // -------------------------------------------------------------------

            $companyData = $this->company->findByCpfForUpdate($requestData['company_cpf']);

            if ($companyData['is_active'] !== 1) {
                $this->database->rollBack();
                $this->response->json(['error', 'Empresa inativa']);
            }

            // -------------------------------------------------------------------
            // Busca cliente com lock exclusivo
            // -------------------------------------------------------------------

            $customerData = $this->customer->findByCpfForUpdate($requestData['customer_cpf']);

            if ($customerData['is_active'] !== 1) {
                $this->database->rollBack();
                $this->response->json(['error', 'Cliente inativo']);
            }

            // -------------------------------------------------------------------
            // Busca grupo com lock exclusivo
            // -------------------------------------------------------------------

            $groupData = $this->group->findForUpdate($customerData['group_id']);

            if (!$groupData) {
                $groupData = ['multiplier_factor' => 1];
            } elseif ($groupData['is_active'] !== 1) {
                $this->database->rollBack();
                $this->response->json(['error', 'Grupo do cliente inativo']);
            }

            // -------------------------------------------------------------------
            // Verifica limite diário de pontuações do cliente
            // -------------------------------------------------------------------

            $dailyCount = $this->score->countDailyByCustomer($customerData['id']);

            if ($dailyCount >= MAX_DAILY_LIMIT_POINTS_PER_CUSTOMER) {
                $this->database->rollBack();
                $this->response->json(['error', 'Limite máximo de pontuação diaria por cliente atingido']);
            }

            // -------------------------------------------------------------------
            // Prepara e insere a pontuação, confirmando a transação
            // -------------------------------------------------------------------

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
                'transaction_code' => bin2hex(random_bytes(8)),
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
            // Monta e retorna o comprovante
            // -------------------------------------------------------------------

            if (!$customerData['fullname']) {
                $customerData['fullname'] = 'Cliente';
            }

            $receipt  = "================================\n";
            $receipt .= "     COMPROVANTE DE PONTUAÇÃO\n";
            $receipt .= "================================\n\n";
            $receipt .= "Cliente: {$customerData['fullname']}\n";
            $receipt .= "Produto: {$requestData['product_name']}\n";
            $receipt .= "Qtd: {$requestData['amount']} L\n";
            $receipt .= "Pontos: {$dataToBeSaved['final_points']}\n";
            $receipt .= "Saldo atual: {$currentBalance}\n";
            $receipt .= "Data: " . date('d/m/Y H:i:s') . "\n";
            $receipt .= "Transação: {$transactionCode}\n\n";
            $receipt .= "================================\n";
            $receipt .= "     Obrigado pela Compra!\n";
            $receipt .= "================================";

            $this->response->json(['success', 'Pontuação registrada com sucesso', $receipt]);

        } catch (\Throwable $e) {

            // -------------------------------------------------------------------
            // Registra erro no log e reverte a transação
            // -------------------------------------------------------------------

            error_log('[ScoreAPI] Erro ao registrar pontuação: ' . (string) $e);
            $this->database->rollBack();
            $this->response->json(['error', 'Erro ao registrar pontuação']);

        }
    }
}
