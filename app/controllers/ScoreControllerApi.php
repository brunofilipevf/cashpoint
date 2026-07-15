<?php

namespace App\Controllers;

class ScoreControllerApi
{
    public function __construct(
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
                'cpf' => $this->request->json()['cpf'],
                'codigo' => $this->request->json()['codigo'],
                'bico' => $this->request->json()['bico'],
                'produto_codigo_barra' => $this->request->json()['produto_codigo_barra'],
                'produto_nome' => $this->request->json()['produto_nome'],
                'quantidade' => $this->request->json()['quantidade'],
                'preco_unit' => $this->request->json()['preco_unit'],
                'valor' => $this->request->json()['valor'],
                'hora' => $this->request->json()['hora']
            ];

            $errors = $this->validator->fields($requestData, [
                'cpf' => 'required|document|exist:customer,cpf',
                'codigo' => 'required|integer|unique:supply,codigo',
                'bico' => 'required|string',
                'produto_codigo_barra' => 'required|integer',
                'produto_nome' => 'required|string',
                'quantidade' => 'required|numeric',
                'preco_unit' => 'required|numeric',
                'valor' => 'required|numeric'
            ], [
                'cpf' => 'CPF/CNPJ',
                'codigo' => 'código',
                'bico' => 'bico',
                'produto_codigo_barra' => 'código de barras',
                'produto_nome' => 'nome do produto',
                'quantidade' => 'quantidade',
                'preco_unit' => 'preço unitário',
                'valor' => 'valor total'
            ]);

            if ($errors) {
                $this->response->json($errors);
            }

            $customerData = $this->customer->findByCpfForUpdate($requestData['cpf']);

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
                'base_points' => $requestData['quantidade'],
                'final_points' => $requestData['quantidade'],
                'user_id' => 1
            ];

            $this->score->insert($dataToBeSaved);
            $this->database->commit();
            $this->response->json('Pontuação registrada com sucesso');

        } catch (\Exception) {
            $this->database->rollBack();
            $this->response->json('Erro ao registrar pontuação');
        }
    }
}
