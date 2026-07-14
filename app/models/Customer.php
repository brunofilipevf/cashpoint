<?php

namespace App\Models;

class Customer
{
    public function __construct(
        private \Core\Database $database
    ) {}

    public function all()
    {
        $sql = "SELECT c.*, g.name AS group_name
                FROM `customer` c
                LEFT JOIN `group` g ON c.group_id = g.id
                ORDER BY c.id DESC";

        return $this->database->selectAll($sql);
    }

    public function find($customerId)
    {
        return $this->database->selectOne("SELECT * FROM `customer` WHERE id = ? LIMIT 1", [$customerId]);
    }

    public function findByCpfForUpdate($cpf)
    {
        return $this->database->selectOne("SELECT * FROM `customer` WHERE cpf = ? LIMIT 1 FOR UPDATE", [$cpf]);
    }

    public function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `customer` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";

        return $this->database->insert($sql, array_values($data));
    }

    public function update($data, $customerId)
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE `customer` SET {$set}, updated_at = NOW() WHERE id = ?";
        $params = array_values($data);
        $params[] = $customerId;

        return $this->database->update($sql, $params);
    }

    public function delete($customerId)
    {
        return $this->database->delete("DELETE FROM `customer` WHERE id = ?", [$customerId]);
    }
}
