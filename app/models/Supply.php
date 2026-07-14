<?php

namespace App\Models;

use Core\Database;

class Supply
{
    public static function all($companyId)
    {
        $sql = "SELECT s.*, p.name AS product_name, COALESCE(a.fullname, a.rfid) AS fullname_or_rfid
                FROM `supply` s
                INNER JOIN `product` p ON s.product_id = p.id
                INNER JOIN `attendant` a ON s.attendant_id = a.id
                WHERE company_id = ?
                ORDER BY s.id DESC";
                
        return Database::selectAll($sql, [$companyId]);
    }

    public static function find($supplyId)
    {
        $sql = "SELECT * FROM `supply` WHERE id = ? LIMIT 1";

        return Database::selectOne($sql, [$supplyId]);
    }

    public static function exist($companyId, $codigo)
    {
        $sql = "SELECT 1 FROM `supply` WHERE company_id = ? AND codigo = ? LIMIT 1";

        return Database::exist($sql, [$companyId, $codigo]);
    }

    public static function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `supply` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";

        return Database::insert($sql, array_values($data));
    }
}
