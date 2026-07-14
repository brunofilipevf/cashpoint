<?php

namespace App\Models;

class Company
{
    public function __construct(
        private \Core\Database $database
    ) {}

    public function all()
    {
        return $this->database->selectAll("SELECT * FROM `company` ORDER BY id DESC");
    }

    public function find($companyId)
    {
        return $this->database->selectOne("SELECT * FROM `company` WHERE id = ? LIMIT 1", [$companyId]);
    }

    public function findByCpfForUpdate($cpf)
    {
        return $this->database->selectOne("SELECT * FROM `company` WHERE cpf = ? LIMIT 1 FOR UPDATE", [$cpf]);
    }

    public function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `company` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";

        return $this->database->insert($sql, array_values($data));
    }

    public function update($data, $companyId)
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE `company` SET {$set}, updated_at = NOW() WHERE id = ?";
        $params = array_values($data);
        $params[] = $companyId;

        return $this->database->update($sql, $params);
    }

    public function delete($companyId)
    {
        return $this->database->delete("DELETE FROM `company` WHERE id = ?", [$companyId]);
    }
}
