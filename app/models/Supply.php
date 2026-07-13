<?php

namespace App\Models;

use Core\Database;

class Supply
{
    public static function all($companyId)
    {
        // -------------------------------------------------------------------
        // Retorna todos os abastecimentos da empresa com produto e frentista
        // -------------------------------------------------------------------

        $sql = "SELECT s.*, p.name AS product_name, a.fullname AS attendant_fullname
                FROM `supply` s
                INNER JOIN `product` p ON s.product_id = p.id
                INNER JOIN `attendant` a ON s.attendant_id = a.id
                WHERE company_id = ?
                ORDER BY s.id DESC";
        return Database::selectAll($sql, [$companyId]);
    }

    public static function get($supplyId)
    {
        // -------------------------------------------------------------------
        // Busca um abastecimento pelo ID
        // -------------------------------------------------------------------

        $sql = "SELECT * FROM `supply` WHERE id = ? LIMIT 1";
        return Database::selectOne($sql, [$supplyId]);
    }

    public static function exist($companyId, $codigo)
    {
        // -------------------------------------------------------------------
        // Verifica se um abastecimento existe ($companyID + $codigo)
        // -------------------------------------------------------------------

        $sql = "SELECT 1 FROM `supply` WHERE company_id = ? AND codigo = ? LIMIT 1";
        return Database::exist($sql, [$companyId, $codigo]);
    }

    public static function insert($data)
    {
        // -------------------------------------------------------------------
        // Insere um novo abastecimento e retorna o ID gerado
        // -------------------------------------------------------------------

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `supply` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";
        return Database::insert($sql, array_values($data));
    }
}
