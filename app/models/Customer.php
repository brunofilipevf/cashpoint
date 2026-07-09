<?php

namespace App\Models;

use Core\Database;

class Customer
{
    public static function all()
    {
        // -------------------------------------------------------------------
        // Retorna todos os clientes com nome do grupo
        // -------------------------------------------------------------------

        $sql = "SELECT c.*, g.name AS group_name
                FROM `customer` c
                LEFT JOIN `group` g ON c.group_id = g.id
                ORDER BY c.id DESC";
        return Database::selectAll($sql);
    }

    public static function get($customerId)
    {
        // -------------------------------------------------------------------
        // Busca um cliente pelo ID
        // -------------------------------------------------------------------

        $sql = "SELECT * FROM `customer` WHERE id = ? LIMIT 1";
        return Database::selectOne($sql, [$customerId]);
    }

    public static function getByCpfForUpdate($customerCpf)
    {
        // -------------------------------------------------------------------
        // Busca cliente por CPF com lock para transações
        // -------------------------------------------------------------------

        $sql = "SELECT * FROM `customer` WHERE cpf = ? LIMIT 1 FOR UPDATE";
        return Database::selectOne($sql, [$customerCpf]);
    }

    public static function insert($data)
    {
        // -------------------------------------------------------------------
        // Insere um novo cliente e retorna o ID gerado
        // -------------------------------------------------------------------

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `customer` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";
        return Database::insert($sql, array_values($data));
    }

    public static function update($data, $customerId)
    {
        // -------------------------------------------------------------------
        // Atualiza um cliente existente pelo ID
        // -------------------------------------------------------------------

        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE `customer` SET {$set}, updated_at = NOW() WHERE id = ?";
        $params = array_values($data);
        $params[] = $customerId;
        return Database::update($sql, $params);
    }

    public static function delete($customerId)
    {
        // -------------------------------------------------------------------
        // Remove um cliente do banco pelo ID
        // -------------------------------------------------------------------

        $sql = "DELETE FROM `customer` WHERE id = ?";
        return Database::delete($sql, [$customerId]);
    }
}
