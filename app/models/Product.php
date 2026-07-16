<?php

namespace App\Models;

class Product
{
    public function __construct(
        private \Core\Database $database
    ) {}

    public function all()
    {
        return $this->database->selectAll("SELECT * FROM `product` ORDER BY id DESC");
    }

    public function find($productId)
    {
        return $this->database->selectOne("SELECT * FROM `product` WHERE id = ? LIMIT 1", [$productId]);
    }

    public function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `product` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";

        return $this->database->insert($sql, array_values($data));
    }

    public function update($data, $productId)
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE `product` SET {$set}, updated_at = NOW() WHERE id = ?";
        $params = array_values($data);
        $params[] = $productId;

        return $this->database->update($sql, $params);
    }

    public function delete($productId)
    {
        return $this->database->delete("DELETE FROM `product` WHERE id = ?", [$productId]);
    }
}
