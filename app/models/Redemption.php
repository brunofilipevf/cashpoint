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

    public static function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO `redemption` ({$columns}) VALUES ({$placeholders})";
        return Database::insert($sql, array_values($data));
    }

    public static function countByAward($awardId)
    {
        # Retorna o número total de resgates globais realizados para um prêmio específico
        $sql = "SELECT COUNT(id) AS total FROM `redemption` WHERE award_id = ?";
        $result = Database::selectOne($sql, [$awardId]);
        return (int) $result['total'];
    }

    public static function countByAwardAndCustomer($awardId, $customerId)
    {
        # Retorna a quantidade de vezes que um cliente específico resgatou um determinado prêmio
        $sql = "SELECT COUNT(id) AS total FROM `redemption` WHERE award_id = ? AND customer_id = ?";
        $result = Database::selectOne($sql, [$awardId, $customerId]);
        return (int) $result['total'];
    }
}
