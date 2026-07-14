<?php

namespace App\Models;

use Core\Database;

class Customer
{
    public static function all()
    {
        $sql = "SELECT c.*, g.name AS group_name
                FROM `customer` c
                LEFT JOIN `group` g ON c.group_id = g.id
                ORDER BY c.id DESC";

        return Database::selectAll($sql);
    }

    public static function find($customerId)
    {
        $sql = "SELECT * FROM `customer` WHERE id = ? LIMIT 1";

        return Database::selectOne($sql, [$customerId]);
    }

    public static function findByCpfForUpdate($cpf)
    {
        $sql = "SELECT * FROM `customer` WHERE cpf = ? LIMIT 1 FOR UPDATE";

        return Database::selectOne($sql, [$cpf]);
    }

    public static function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `customer` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";

        return Database::insert($sql, array_values($data));
    }

    public static function update($data, $customerId)
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE `customer` SET {$set}, updated_at = NOW() WHERE id = ?";
        $params = array_values($data);
        $params[] = $customerId;

        return Database::update($sql, $params);
    }

    public static function delete($customerId)
    {
        $sql = "DELETE FROM `customer` WHERE id = ?";

        return Database::delete($sql, [$customerId]);
    }
}
