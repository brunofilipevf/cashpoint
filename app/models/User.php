<?php

namespace App\Models;

class User
{
    public function __construct(
        private \Core\Database $database
    ) {}

    public function all()
    {
        $sql = "SELECT u.*, l.name AS level_name, c.name AS company_name
                FROM `user` u
                INNER JOIN `level` l ON u.level_id = l.id
                LEFT JOIN `company` c ON u.company_id = c.id
                ORDER BY u.id DESC";
        $results = $this->database->selectAll($sql);

        foreach ($results as &$row) {
            unset($row['password']);
        }

        return $results;
    }

    public function find($userId)
    {
        $sql = "SELECT u.*, l.hierarchy
                FROM `user` u
                INNER JOIN `level` l ON u.level_id = l.id
                WHERE u.id = ?
                LIMIT 1";
        $result = $this->database->selectOne($sql, [$userId]);

        if ($result) {
            unset($result['password']);
        }

        return $result;
    }

    public function insert($data)
    {
        $data = $this->hashPassword($data);

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `user` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";

        return $this->database->insert($sql, array_values($data));
    }

    public function update($data, $userId)
    {
        $data = $this->hashPassword($data);

        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE `user` SET {$set}, updated_at = NOW() WHERE id = ?";
        $params = array_values($data);
        $params[] = $userId;

        return $this->database->update($sql, $params);
    }

    public function delete($userId)
    {
        return $this->database->delete("DELETE FROM `user` WHERE id = ?", [$userId]);
    }

    private function hashPassword($data)
    {
        if (isset($data['password']) && $data['password'] !== null && $data['password'] !== '') {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        return $data;
    }
}
