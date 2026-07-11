<?php

namespace App\Models;

use Core\Database;

class Supply
{
    public static function all()
    {
        // -------------------------------------------------------------------
        // Retorna todos os abastecimentos ordenados por ID
        // -------------------------------------------------------------------

        $sql = "SELECT * FROM `supply` ORDER BY id DESC";
        return Database::selectAll($sql);
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
