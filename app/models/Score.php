<?php

namespace App\Models;

class Score
{
    public function __construct(
        private \Core\Database $database
    ) {}

    public function all()
    {
        $sql = "SELECT s.*, c.cpf, u.username, COALESCE(c.fullname, c.cpf) AS fullname_or_cpf
                FROM `score` s
                INNER JOIN `customer` c ON s.customer_id = c.id
                LEFT JOIN `user` u ON s.user_id = u.id
                ORDER BY s.id DESC";

        return $this->database->selectAll($sql);
    }

    public function find($scoreId)
    {
        $sql = "SELECT s.*, c.cpf, u.username, COALESCE(c.fullname, c.cpf) AS fullname_or_cpf
                FROM `score` s
                INNER JOIN `customer` c ON s.customer_id = c.id
                LEFT JOIN `user` u ON s.user_id = u.id
                WHERE s.id = ?
                LIMIT 1";

        return $this->database->selectOne($sql, [$scoreId]);
    }

    public function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `score` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";

        return $this->database->insert($sql, array_values($data));
    }

    public function countDailyByCustomer($customerId)
    {
        return $this->database->count("SELECT COUNT(id) FROM `score` WHERE customer_id = ? AND DATE(created_at) = CURDATE()", [$customerId]);
    }

    public function findBalanceFromCustomer($customerId)
    {
        $sql = "SELECT
                (SELECT COALESCE(SUM(final_points), 0.00) FROM `score` WHERE customer_id = ?) as total_earned,
                (SELECT COALESCE(SUM(points_used), 0.00) FROM `redemption` WHERE customer_id = ?) as total_used,
                (SELECT COALESCE(SUM(final_points), 0.00) FROM `score` WHERE customer_id = ?) -
                (SELECT COALESCE(SUM(points_used), 0.00) FROM `redemption` WHERE customer_id = ?) as balance";

        return $this->database->selectOne($sql, [$customerId, $customerId, $customerId, $customerId]);
    }

    public function findBySupplyCode($supplyCode)
    {
        return $this->database->selectOne("SELECT * FROM `score` WHERE supply_code = ? LIMIT 1", [$supplyCode]);
    }
}
