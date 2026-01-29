<?php

namespace App\Models;

use Services\BaseModel;

class User extends BaseModel
{
    protected $table = 'users';
    protected $columns = ['id', 'username', 'password', 'fullname', 'level_id', 'is_active', 'created_at'];
    protected $protecteds = ['password'];

    protected function findAll()
    {
        $columns = array_diff($this->columns, $this->protecteds);

        $select = [];

        foreach ($columns as $column) {
            $select[] = "u.{$column}";
        }

        $select[] = "l.name as level_name";

        $columns = implode(',', $select);

        $sql = "SELECT {$columns}
                FROM {$this->table} u
                INNER JOIN levels l ON l.id = u.level_id
                ORDER BY u.id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([]);
        return $stmt->fetchAll();
    }
}
