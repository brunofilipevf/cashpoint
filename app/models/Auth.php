<?php

namespace App\Models;

use Core\Database;

class Auth
{
    public static function attempt($data)
    {
        $sql = "SELECT id, password FROM `user` WHERE username = ? AND is_active = 1 LIMIT 1";
        $user = Database::selectOne($sql, [$data['username']]);

        if ($user === false) {
            return false;
        }

        if (!password_verify($data['password'], $user['password'])) {
            return false;
        }

        return $user['id'];
    }
}
