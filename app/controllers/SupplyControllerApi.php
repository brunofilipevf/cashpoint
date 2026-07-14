<?php

namespace App\Controllers;

use App\Models\{Attendant, Company, Product, Supply};
use Core\{Database, Request, Response};

class SupplyControllerApi
{
    public static function insert()
    {
        // ---------------------------------------------------------------
        // Obtém todos os cabeçalhos da requisição
        // ---------------------------------------------------------------

        $headers = getallheaders();

        // ---------------------------------------------------------------
        // Valida o token de autorização Bearer
        // ---------------------------------------------------------------

        if (isset($headers['Authorization'])) {
            $token = $headers['Authorization'];
            $token = str_replace('Bearer ', '', $token);
        } else {
            $token = null;
        }

        if ($token === null || $token === '' || !hash_equals(API_TOKEN, $token)) {
            Response::json(['danger', 'Token inválido ou não informado'], 401);
        }

        // ---------------------------------------------------------------
        // Obtém o CPF/CNPJ da empresa no cabeçalho
        // ---------------------------------------------------------------

        if (isset($headers['X-Company-Document'])) {
            $companyDocument = $headers['X-Company-Document'];
        } else {
            $companyDocument = null;
        }

        if ($companyDocument === null || $companyDocument === '') {
            Response::json(['danger', 'CPF/CNPJ da empresa não informado'], 400);
        }

        // ---------------------------------------------------------------
        // Busca a empresa e verifica se está ativa
        // ---------------------------------------------------------------

        $companyData = Company::findByCpf($companyDocument);

        if (!$companyData || $companyData['is_active'] !== 1) {
            Response::json(['danger', 'Empresa não encontrada ou inativa'], 404);
        }

        // ---------------------------------------------------------------
        // Obtém os dados JSON da requisição
        // ---------------------------------------------------------------

        $input = Request::json();

        if (!isset($input['supply'])) {
            Response::json(['danger', 'Nenhum abastecimento enviado'], 400);
        }

        // ---------------------------------------------------------------
        // Verifica se o abastecimento já foi enviado
        // ---------------------------------------------------------------

        $supply = $input['supply'];

        if (Supply::exist($companyData['id'], $supply['codigo'])) {
            Response::json(['danger', 'Abastecimento já enviado anteriormente'], 409);
        }

        // ---------------------------------------------------------------
        // Inicia transação para garantir consistência
        // ---------------------------------------------------------------

        Database::beginTransaction();

        try {

            // ---------------------------------------------------------------
            // Busca ou cadastra o frentista pelo RFID
            // ---------------------------------------------------------------

            $attendantData = Attendant::findByRfid($supply['rfid_cartao']);

            if (!$attendantData) {
                $attendantId = Attendant::insert([
                    'rfid' => $supply['rfid_cartao']
                ]);

                $attendantData = ['id' => $attendantId];
            }

            // ---------------------------------------------------------------
            // Busca ou cadastra o produto pelo código de barras
            // ---------------------------------------------------------------

            $productData = Product::findByBarcodeForUpdate($supply['produto_codigo_barra']);

            if (!$productData) {
                $productId = Product::insert([
                    'name' => $supply['produto_nome'],
                    'barcode' => $supply['produto_codigo_barra']
                ]);

                $productData = ['id' => $productId];
            }

            // ---------------------------------------------------------------
            // Obtém o IP da requisição
            // ---------------------------------------------------------------

            $ip = Request::ip();

            // ---------------------------------------------------------------
            // Prepara e insere os dados do abastecimento
            // ---------------------------------------------------------------

            $dataToBeSaved = [
                'company_id' => $companyData['id'],
                'codigo' => $supply['codigo'],
                'bico' => $supply['bico'],
                'product_id' => $productData['id'],
                'quantidade' => $supply['quantidade'],
                'preco_unit' => $supply['preco_unit'],
                'valor' => $supply['valor'],
                'hora' => $supply['hora'],
                'attendant_id' => $attendantData['id'],
                'ip' => $ip
            ];

            Supply::insert($dataToBeSaved);
            Database::commit();
            Response::json(['success', 'Abastecimento adicionado com sucesso'], 201);

        } catch (\Exception) {
            // ---------------------------------------------------------------
            // Reverte a transação em caso de erro
            // ---------------------------------------------------------------

            Database::rollBack();
            Response::json(['danger', 'Erro ao adicionar abastecimento'], 500);
        }
    }
}
