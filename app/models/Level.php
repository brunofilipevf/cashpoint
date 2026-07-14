<?php

namespace App\Models;

class Level
{
    public function __construct(
        private \Core\Database $database
    ) {}

    public function all()
    {
        return $this->database->selectAll("SELECT * FROM `level` ORDER BY id DESC");
    }

    public function find($levelId)
    {
        return $this->database->selectOne("SELECT * FROM `level` WHERE id = ? LIMIT 1", [$levelId]);
    }
}
