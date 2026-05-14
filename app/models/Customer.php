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
        return Database::select($sql);
    }

    public static function get($id)
    {
        $sql = "SELECT * FROM `customer` WHERE id = ? LIMIT 1";
        return Database::selectOne($sql, [$id]);
    }

    public static function getByCpf($cpf)
    {
        $sql = "SELECT * FROM `customer` WHERE cpf = ? LIMIT 1";
        return Database::selectOne($sql, [$cpf]);
    }

    public static function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO `customer` ({$columns}) VALUES ({$placeholders})";
        return Database::insert($sql, array_values($data));
    }

    public static function update($data, $id)
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';

        $sql = "UPDATE `customer` SET {$set} WHERE id = ?";
        $params = array_values($data);
        $params[] = $id;

        return Database::update($sql, $params);
    }

    public static function delete($id)
    {
        $sql = "DELETE FROM `customer` WHERE id = ?";
        return Database::delete($sql, [$id]);
    }

    public static function getBalance($id)
    {
        # Calcula o saldo atual do cliente subtraindo os pontos utilizados dos pontos adquiridos
        $sql = "SELECT
                    (SELECT COALESCE(SUM(final_points), 0) FROM `score` WHERE customer_id = ?) -
                    (SELECT COALESCE(SUM(points_used), 0) FROM `redemption` WHERE customer_id = ?) AS balance";

        $result = Database::selectOne($sql, [$id, $id]);
        return (float) $result['balance'];
    }
}
