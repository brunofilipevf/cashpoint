<?php

namespace App\Models;

use Core\Database;

class Company
{
    public static function all()
    {
        // -------------------------------------------------------------------
        // Retorna todas as empresas ordenadas por ID
        // -------------------------------------------------------------------

        $sql = "SELECT * FROM `company` ORDER BY id DESC";
        return Database::selectAll($sql);
    }

    public static function get($companyId)
    {
        // -------------------------------------------------------------------
        // Busca uma empresa pelo ID
        // -------------------------------------------------------------------

        $sql = "SELECT * FROM `company` WHERE id = ? LIMIT 1";
        return Database::selectOne($sql, [$companyId]);
    }

    public static function insert($data)
    {
        // -------------------------------------------------------------------
        // Insere uma nova empresa e retorna o ID gerado
        // -------------------------------------------------------------------

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `company` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";
        return Database::insert($sql, array_values($data));
    }

    public static function update($data, $companyId)
    {
        // -------------------------------------------------------------------
        // Atualiza uma empresa existente pelo ID
        // -------------------------------------------------------------------

        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE `company` SET {$set}, updated_at = NOW() WHERE id = ?";
        $params = array_values($data);
        $params[] = $companyId;
        return Database::update($sql, $params);
    }

    public static function delete($companyId)
    {
        // -------------------------------------------------------------------
        // Remove uma empresa do banco pelo ID
        // -------------------------------------------------------------------

        $sql = "DELETE FROM `company` WHERE id = ?";
        return Database::delete($sql, [$companyId]);
    }
}
