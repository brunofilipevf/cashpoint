<?php

namespace App\Models;

use Core\Database;

class Level
{
    public function __construct(
        private Database $db
    ) { }

    public function all()
    {
        $sql = "SELECT * FROM `level` ORDER BY id DESC";
        return $this->db->select($sql);
    }

    public function get($id)
    {
        $sql = "SELECT * FROM `level` WHERE id = ? LIMIT 1";
        return $this->db->selectOne($sql, [$id]);
    }
}
