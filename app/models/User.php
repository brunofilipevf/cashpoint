<?php

namespace App\Models;

use Core\Database;

class User
{
    public function __construct(
        private Database $db
    ) { }

    public function all()
    {
        $sql = "SELECT u.*, l.name AS level_name, c.name AS company_name
                FROM `user` u
                INNER JOIN `level` l ON u.level_id = l.id
                LEFT JOIN `company` c ON u.company_id = c.id
                ORDER BY u.id DESC";
        return $this->db->select($sql);
    }

    public function get($id)
    {
        $sql = "SELECT u.*, l.hierarchy
                FROM `user` u
                INNER JOIN `level` l ON u.level_id = l.id
                WHERE u.id = ?
                LIMIT 1";
        return $this->db->selectOne($sql, [$id]);
    }

    public function insert($data)
    {
        $data = $this->hashPassword($data);
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `user` ({$columns}) VALUES ({$placeholders})";
        return $this->db->insert($sql, array_values($data));
    }

    public function update($data, $id)
    {
        $data = $this->hashPassword($data);
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE `user` SET {$set} WHERE id = ?";
        $params = array_values($data);
        $params[] = $id;
        return $this->db->update($sql, $params);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM `user` WHERE id = ?";
        return $this->db->delete($sql, [$id]);
    }

    private function hashPassword($data)
    {
        if (isset($data['password']) && $data['password'] !== null && $data['password'] !== '') {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']);
        }

        return $data;
    }
}
