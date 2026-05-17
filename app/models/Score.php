<?php

namespace App\Models;

use Core\Database;

class Score
{
    public function __construct(
        private Database $db
    ) { }

    public function all()
    {
        $sql = "SELECT s.*, c.cpf, u.username
                FROM `score` s
                INNER JOIN `customer` c ON s.customer_id = c.id
                INNER JOIN `user` u ON s.user_id = u.id
                ORDER BY s.id DESC";
        return $this->db->select($sql);
    }

    public function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `score` ({$columns}) VALUES ({$placeholders})";
        return $this->db->insert($sql, array_values($data));
    }
}
