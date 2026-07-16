<?php

namespace App\Models;

class Redemption
{
    public function __construct(
        private \Core\Database $database
    ) {}

    public function all()
    {
        $sql = "SELECT r.*, c.cpf, u.username, a.name AS award_name, COALESCE(c.fullname, c.cpf) AS fullname_or_cpf
                FROM `redemption` r
                INNER JOIN `customer` c ON r.customer_id = c.id
                INNER JOIN `award` a ON r.award_id = a.id
                INNER JOIN `user` u ON r.user_id = u.id
                ORDER BY r.id DESC";

        return $this->database->selectAll($sql);
    }

    public function find($redemptionId)
    {
        $sql = "SELECT r.*, c.cpf, u.username, a.name AS award_name, COALESCE(c.fullname, c.cpf) AS fullname_or_cpf
                FROM `redemption` r
                INNER JOIN `customer` c ON r.customer_id = c.id
                INNER JOIN `award` a ON r.award_id = a.id
                INNER JOIN `user` u ON r.user_id = u.id
                WHERE r.id = ?
                LIMIT 1";

        return $this->database->selectOne($sql, [$redemptionId]);
    }

    public function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `redemption` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";

        return $this->database->insert($sql, array_values($data));
    }

    public function countByAward($awardId)
    {
        return $this->database->count("SELECT COUNT(id) FROM `redemption` WHERE award_id = ?", [$awardId]);
    }

    public function countByAwardAndCustomer($awardId, $customerId)
    {
        return $this->database->count("SELECT COUNT(id) FROM `redemption` WHERE award_id = ? AND customer_id = ?", [$awardId, $customerId]);
    }
}
