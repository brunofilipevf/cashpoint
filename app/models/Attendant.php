<?php

namespace App\Models;

use Core\Database;

class Attendant
{
    public static function all()
    {
        $sql = "SELECT * FROM `attendant` ORDER BY id DESC";
        
        return Database::selectAll($sql);
    }

    public static function find($attendantId)
    {
        $sql = "SELECT * FROM `attendant` WHERE id = ? LIMIT 1";

        return Database::selectOne($sql, [$attendantId]);
    }

    public static function findByRfid($rfid)
    {
        $sql = "SELECT * FROM `attendant` WHERE rfid = ? LIMIT 1";

        return Database::selectOne($sql, [$rfid]);
    }

    public static function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `attendant` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";

        return Database::insert($sql, array_values($data));
    }

    public static function update($data, $attendantId)
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE `attendant` SET {$set}, updated_at = NOW() WHERE id = ?";
        $params = array_values($data);
        $params[] = $attendantId;

        return Database::update($sql, $params);
    }

    public static function delete($attendantId)
    {
        $sql = "DELETE FROM `attendant` WHERE id = ?";

        return Database::delete($sql, [$attendantId]);
    }
}
