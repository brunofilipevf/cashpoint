<?php

namespace App\Models;

use Core\Database;

class Redemption
{
    public static function all()
    {
        $sql = "SELECT r.*,
                       COALESCE(c.fullname, c.cpf) AS customer,
                       a.name AS award_name,
                       u.username AS username
                FROM `redemption` r
                INNER JOIN `customer` c ON r.customer_id = c.id
                INNER JOIN `award` a ON r.award_id = a.id
                INNER JOIN `user` u ON r.user_id = u.id
                ORDER BY r.id DESC";
        return Database::select($sql);
    }

    public static function add($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO `redemption` ({$columns}) VALUES ({$placeholders})";
        return Database::insert($sql, array_values($data));
    }
}
