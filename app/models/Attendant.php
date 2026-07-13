<?php

namespace App\Models;

use Core\Database;

class Attendant
{
    public static function all()
    {
        // -------------------------------------------------------------------
        // Retorna todos os frentistas ordenados por ID
        // -------------------------------------------------------------------

        $sql = "SELECT * FROM `attendant` ORDER BY id DESC";
        return Database::selectAll($sql);
    }

    public static function get($attendantId)
    {
        // -------------------------------------------------------------------
        // Busca um frentista pelo ID
        // -------------------------------------------------------------------

        $sql = "SELECT * FROM `attendant` WHERE id = ? LIMIT 1";
        return Database::selectOne($sql, [$attendantId]);
    }

    public static function getByRfid($rfid)
    {
        // -------------------------------------------------------------------
        // Busca um frentista pelo RFID com lock para transações
        // -------------------------------------------------------------------

        $sql = "SELECT * FROM `attendant` WHERE rfid = ? LIMIT 1 FOR UPDATE";
        return Database::selectOne($sql, [$rfid]);
    }

    public static function insert($data)
    {
        // -------------------------------------------------------------------
        // Insere um novo frentista e retorna o ID gerado
        // -------------------------------------------------------------------

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `attendant` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";
        return Database::insert($sql, array_values($data));
    }

    public static function update($data, $attendantId)
    {
        // -------------------------------------------------------------------
        // Atualiza um frentista existente pelo ID
        // -------------------------------------------------------------------

        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE `attendant` SET {$set}, updated_at = NOW() WHERE id = ?";
        $params = array_values($data);
        $params[] = $attendantId;
        return Database::update($sql, $params);
    }

    public static function delete($attendantId)
    {
        // -------------------------------------------------------------------
        // Remove um frentista do banco pelo ID
        // -------------------------------------------------------------------

        $sql = "DELETE FROM `attendant` WHERE id = ?";
        return Database::delete($sql, [$attendantId]);
    }
}
