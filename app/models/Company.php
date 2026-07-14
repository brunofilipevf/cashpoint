<?php

namespace App\Models;

use Core\Database;

class Company
{
    public static function all()
    {
        $sql = "SELECT * FROM `company` ORDER BY id DESC";

        return Database::selectAll($sql);
    }

    public static function find($companyId)
    {
        $sql = "SELECT * FROM `company` WHERE id = ? LIMIT 1";

        return Database::selectOne($sql, [$companyId]);
    }

    public static function findByCpf($cpf)
    {
        $sql = "SELECT * FROM `company` WHERE cpf = ? LIMIT 1";

        return Database::selectOne($sql, [$cpf]);
    }

    public static function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `company` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";

        return Database::insert($sql, array_values($data));
    }

    public static function update($data, $companyId)
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE `company` SET {$set}, updated_at = NOW() WHERE id = ?";
        $params = array_values($data);
        $params[] = $companyId;

        return Database::update($sql, $params);
    }

    public static function delete($companyId)
    {
        $sql = "DELETE FROM `company` WHERE id = ?";

        return Database::delete($sql, [$companyId]);
    }
}
