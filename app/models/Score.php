<?php

namespace App\Models;

use Core\Database;

class Score
{
    public static function all()
    {
        $sql = "SELECT s.*,
                       COALESCE(c.fullname, c.cpf) AS customer,
                       u.username AS username
                FROM `score` s
                INNER JOIN `customer` c ON s.customer_id = c.id
                INNER JOIN `user` u ON s.user_id = u.id
                ORDER BY s.id DESC";
        return Database::select($sql);
    }

    public static function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO `score` ({$columns}) VALUES ({$placeholders})";
        return Database::insert($sql, array_values($data));
    }
}
