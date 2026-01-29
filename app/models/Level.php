<?php

namespace App\Models;

use Services\BaseModel;

class Level extends BaseModel
{
    protected $table = 'levels';
    protected $columns = ['id', 'name', 'hierarchy', 'description', 'created_at'];

    protected function findAll()
    {
        $columns = implode(',', $this->columns);

        $sql = "SELECT {$columns} FROM {$this->table} ORDER BY hierarchy ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([]);
        return $stmt->fetchAll();
    }
}
