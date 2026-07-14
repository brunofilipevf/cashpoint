<?php

namespace App\Models;

use Core\Database;

class Level
{
    public static function all()
    {
        $sql = "SELECT * FROM `level` ORDER BY id DESC";
        
        return Database::selectAll($sql);
    }

    public static function find($levelId)
    {
        $sql = "SELECT * FROM `level` WHERE id = ? LIMIT 1";

        return Database::selectOne($sql, [$levelId]);
    }
}
