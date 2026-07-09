<?php

namespace App\Models;

use Core\Database;

class Level
{
    public static function all()
    {
        // -------------------------------------------------------------------
        // Retorna todos os níveis de acesso
        // -------------------------------------------------------------------

        $sql = "SELECT * FROM `level` ORDER BY id DESC";
        return Database::selectAll($sql);
    }

    public static function get($levelId)
    {
        // -------------------------------------------------------------------
        // Busca um nível de acesso pelo ID
        // -------------------------------------------------------------------

        $sql = "SELECT * FROM `level` WHERE id = ? LIMIT 1";
        return Database::selectOne($sql, [$levelId]);
    }
}
