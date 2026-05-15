<?php

namespace App\Models;

use Core\Database;

class Product
{
    public function __construct(
        private Database $db
    ) { }

    public function all()
    {
        $sql = "SELECT * FROM `product` ORDER BY id DESC";
        return $this->db->select($sql);
    }

    public function get($id)
    {
        $sql = "SELECT * FROM `product` WHERE id = ? LIMIT 1";
        return $this->db->selectOne($sql, [$id]);
    }

    public function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `product` ({$columns}) VALUES ({$placeholders})";
        return $this->db->insert($sql, array_values($data));
    }

    public function update($data, $id)
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE `product` SET {$set} WHERE id = ?";
        $params = array_values($data);
        $params[] = $id;
        return $this->db->update($sql, $params);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM `product` WHERE id = ?";
        return $this->db->delete($sql, [$id]);
    }
}
