<?php

namespace App\Models;

class Group
{
    public function __construct(
        private \Core\Database $database
    ) {}

    public function all()
    {
        return $this->database->selectAll("SELECT * FROM `group` ORDER BY id DESC");
    }

    public function find($groupId)
    {
        return $this->database->selectOne("SELECT * FROM `group` WHERE id = ? LIMIT 1", [$groupId]);
    }

    public function findForUpdate($groupId)
    {
        return $this->database->selectOne("SELECT * FROM `group` WHERE id = ? LIMIT 1 FOR UPDATE", [$groupId]);
    }

    public function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `group` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";

        return $this->database->insert($sql, array_values($data));
    }

    public function update($data, $groupId)
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE `group` SET {$set}, updated_at = NOW() WHERE id = ?";
        $params = array_values($data);
        $params[] = $groupId;

        return $this->database->update($sql, $params);
    }

    public function delete($groupId)
    {
        return $this->database->delete("DELETE FROM `group` WHERE id = ?", [$groupId]);
    }
}
