<?php

namespace App\Models;

use Core\Database;

class Group
{
    public static function all()
    {
        $sql = "SELECT * FROM `group` ORDER BY id DESC";
        
        return Database::selectAll($sql);
    }

    public static function find($groupId)
    {
        $sql = "SELECT * FROM `group` WHERE id = ? LIMIT 1";

        return Database::selectOne($sql, [$groupId]);
    }

    public static function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `group` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";

        return Database::insert($sql, array_values($data));
    }

    public static function update($data, $groupId)
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE `group` SET {$set}, updated_at = NOW() WHERE id = ?";
        $params = array_values($data);
        $params[] = $groupId;

        return Database::update($sql, $params);
    }

    public static function delete($groupId)
    {
        $sql = "DELETE FROM `group` WHERE id = ?";

        return Database::delete($sql, [$groupId]);
    }
}
