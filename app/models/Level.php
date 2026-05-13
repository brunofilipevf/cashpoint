<?php

namespace App\Models;

use Core\Database;

class Level
{
    public static function all()
    {
        $sql = "SELECT * FROM `level` ORDER BY id DESC";
        return Database::select($sql);
    }

    public static function get($id)
    {
        $sql = "SELECT * FROM `level` WHERE id = ? LIMIT 1";
        return Database::selectOne($sql, [$id]);
    }

    public static function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO `level` ({$columns}) VALUES ({$placeholders})";
        return Database::insert($sql, array_values($data));
    }

    public static function update($data, $id)
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';

        $sql = "UPDATE `level` SET {$set} WHERE id = ?";
        $params = array_values($data);
        $params[] = $id;

        return Database::update($sql, $params);
    }

    public static function delete($id)
    {
        $sql = "DELETE FROM `level` WHERE id = ?";
        return Database::delete($sql, [$id]);
    }
}
