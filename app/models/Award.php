<?php

namespace App\Models;

class Award
{
    public function __construct(
        private \Core\Database $database
    ) {}

    public function all()
    {
        $sql = "SELECT a.*, p.name AS product_name,
                    CASE
                        WHEN a.is_active = 1 AND a.end_date < NOW() THEN 0
                        ELSE a.is_active
                    END AS is_active
                FROM `award` a
                INNER JOIN `product` p ON a.product_id = p.id
                ORDER BY a.id DESC";

        return $this->database->selectAll($sql);
    }

    public function allAvailable()
    {
        $sql = "SELECT *
                FROM `award`
                WHERE start_date <= NOW()
                  AND end_date >= NOW()
                  AND is_active = 1
                ORDER BY id DESC";

        return $this->database->selectAll($sql);
    }

    public function find($awardId)
    {
        return $this->database->selectOne("SELECT * FROM `award` WHERE id = ? LIMIT 1", [$awardId]);
    }

    public function findForUpdate($awardId)
    {
        return $this->database->selectOne("SELECT * FROM `award` WHERE id = ? LIMIT 1 FOR UPDATE", [$awardId]);
    }

    public function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `award` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";

        return $this->database->insert($sql, array_values($data));
    }

    public function update($data, $awardId)
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE `award` SET {$set}, updated_at = NOW() WHERE id = ?";
        $params = array_values($data);
        $params[] = $awardId;

        return $this->database->update($sql, $params);
    }

    public function delete($awardId)
    {
        return $this->database->delete("DELETE FROM `award` WHERE id = ?", [$awardId]);
    }
}
