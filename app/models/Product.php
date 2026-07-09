<?php

namespace App\Models;

use Core\Database;

class Product
{
    public static function all()
    {
        // -------------------------------------------------------------------
        // Retorna todos os produtos ordenados por ID
        // -------------------------------------------------------------------

        $sql = "SELECT * FROM `product` ORDER BY id DESC";
        return Database::selectAll($sql);
    }

    public static function get($productId)
    {
        // -------------------------------------------------------------------
        // Busca um produto pelo ID
        // -------------------------------------------------------------------

        $sql = "SELECT * FROM `product` WHERE id = ? LIMIT 1";
        return Database::selectOne($sql, [$productId]);
    }

    public static function insert($data)
    {
        // -------------------------------------------------------------------
        // Insere um novo produto e retorna o ID gerado
        // -------------------------------------------------------------------

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `product` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";
        return Database::insert($sql, array_values($data));
    }

    public static function update($data, $productId)
    {
        // -------------------------------------------------------------------
        // Atualiza um produto existente pelo ID
        // -------------------------------------------------------------------

        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE `product` SET {$set}, updated_at = NOW() WHERE id = ?";
        $params = array_values($data);
        $params[] = $productId;
        return Database::update($sql, $params);
    }

    public static function delete($productId)
    {
        // -------------------------------------------------------------------
        // Remove um produto do banco pelo ID
        // -------------------------------------------------------------------

        $sql = "DELETE FROM `product` WHERE id = ?";
        return Database::delete($sql, [$productId]);
    }
}
