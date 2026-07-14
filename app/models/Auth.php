<?php

namespace App\Models;

use Core\Database;

class Auth
{
    private static $data = [];

    public static function login($username, $password)
    {
        $sql = "SELECT id, password FROM `user` WHERE username = ? AND is_active = 1 LIMIT 1";
        $user = Database::selectOne($sql, [$username]);

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        return $user['id'];
    }

    public static function store($data)
    {
        self::$data = $data;
    }

    public static function stored()
    {
        return self::$data;
    }
}
