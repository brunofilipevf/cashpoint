<?php

namespace App\Models;

use Core\Database;

class Auth
{
    public function __construct(
        private Database $db
    ) { }

    public function attempt($data)
    {
        $sql = "SELECT id, password FROM `user` WHERE username = ? AND is_active = 1 LIMIT 1";
        $user = $this->db->selectOne($sql, [$data['username']]);

        if ($user === false) {
            return false;
        }

        if (!password_verify($data['password'], $user['password'])) {
            return false;
        }

        return $user['id'];
    }
}
