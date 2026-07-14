<?php

namespace App\Models;

use Core\Database;

class Product
{
    public static function all()
    {
        $sql = "SELECT * FROM `product` ORDER BY id DESC";

        return Database::selectAll($sql);
    }

    public static function find($productId)
    {
        $sql = "SELECT * FROM `product` WHERE id = ? LIMIT 1";

        return Database::selectOne($sql, [$productId]);
    }

    public static function findByBarcodeForUpdate($barcode)
    {
        $sql = "SELECT * FROM `product` WHERE barcode = ? LIMIT 1 FOR UPDATE";

        return Database::selectOne($sql, [$barcode]);
    }

    public static function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `product` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";

        return Database::insert($sql, array_values($data));
    }

    public static function update($data, $productId)
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE `product` SET {$set}, updated_at = NOW() WHERE id = ?";
        $params = array_values($data);
        $params[] = $productId;

        return Database::update($sql, $params);
    }

    public static function delete($productId)
    {
        $sql = "DELETE FROM `product` WHERE id = ?";

        return Database::delete($sql, [$productId]);
    }
}
