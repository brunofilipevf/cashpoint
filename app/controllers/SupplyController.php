<?php

namespace App\Controllers;

use App\Models\{Auth, Attendant, Company, Product, Supply};
use Core\{Database, Request, Response};

class SupplyController
{
    public static function index()
    {
        $authUserData = Auth::stored();

        Response::view('supply/index', [
            'supplies' => Supply::all($authUserData['company_id'])
        ]);
    }

    public static function show($supplyId)
    {
        $supplyData = Supply::get($supplyId);

        if (!$supplyData) {
            Response::abort(404);
        }

        Response::view('supply/edit', [
            'supply' => $supplyData
        ]);
    }

    public static function insert()
    {
        // -------------------------------------------------------------------
        // Valida o token de autorização Bearer
        // -------------------------------------------------------------------

        $headers = getallheaders();

        if (isset($headers['Authorization'])) {
            $token = $headers['Authorization'];
        } else {
            $token = null;
        }

        if ($token !== null) {
            $token = str_replace('Bearer ', '', $token);
        }

        if ($token === null || !hash_equals(API_TOKEN, $token)) {
            Response::json(['fail', 'Token inválido'], 401);
        }

        // -------------------------------------------------------------------
        // Busca a empresa pelo CPF/CNPJ enviado no cabeçalho
        // -------------------------------------------------------------------

        if (isset($headers['X-Company-Document'])) {
            $companyDocument = $headers['X-Company-Document'];
        } else {
            $companyDocument = null;
        }

        if ($companyDocument === null) {
            Response::json(['fail', 'CPF/CNPJ da empresa não informado'], 400);
        }

        $companyData = Company::getByCpf($companyDocument);

        if (!$companyData) {
            Response::json(['fail', 'Empresa não encontrada'], 404);
        }

        if ($companyData['is_active'] !== 1) {
            Response::json(['fail', 'Empresa inativa'], 403);
        }

        // -------------------------------------------------------------------
        // Verifica se o abastecimento já foi enviado
        // -------------------------------------------------------------------

        $input = Request::json();

        if (!isset($input['supply'])) {
            Response::json(['fail', 'Nenhum abastecimento enviado'], 400);
        }

        $supply = $input['supply'];

        if (Supply::exist($companyData['id'], $supply['codigo'])) {
            Response::json(['fail', 'Abastecimento já enviado anteriormente'], 409);
        }

        // -------------------------------------------------------------------
        // Inicia transação para garantir consistência
        // -------------------------------------------------------------------

        Database::beginTransaction();

        try {
            // ---------------------------------------------------------------
            // Busca ou cadastra o produto pelo código de barras
            // ---------------------------------------------------------------

            $attendantData = Attendant::getByRfidForUpdate($supply['rfid_cartao']);

            if (!$attendantData) {
                $attendantId = Attendant::insert([
                    'rfid' => $supply['rfid_cartao']
                ]);

                if (!$attendantId) {
                    Database::rollBack();
                    Response::json(['fail', 'Erro ao adicionar frentista'], 500);
                }

                $attendantData = ['id' => $attendantId];
            }

            // ---------------------------------------------------------------
            // Busca ou cadastra o frentista pelo RFID
            // ---------------------------------------------------------------

            $productData = Product::getByBarcodeForUpdate($supply['produto_codigo_barra']);

            if (!$productData) {
                $productId = Product::insert([
                    'name' => $supply['produto_nome'],
                    'barcode' => $supply['produto_codigo_barra']
                ]);

                if (!$productId) {
                    Database::rollBack();
                    Response::json(['fail', 'Erro ao adicionar produto'], 500);
                }

                $productData = ['id' => $productId];
            }

            // ---------------------------------------------------------------
            // Prepara os dados do abastecimento
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
                'ip' => Request::ip()
            ];

            // ---------------------------------------------------------------
            // Insere o abastecimento e confirma a transação
            // ---------------------------------------------------------------

            Supply::insert($dataToBeSaved);
            Database::commit();
            Response::json(['success', 'Abastecimento adicionado com sucesso'], 201);

        } catch (\Exception $e) {
            // ---------------------------------------------------------------
            // Reverte a transação em caso de erro
            // ---------------------------------------------------------------

            Database::rollBack();
            error_log((string) $e);
            Response::json(['fail', 'Erro ao adicionar abastecimento'], 500);
        }
    }
}
