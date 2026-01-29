<?php

namespace App\Models;

use Services\BaseModel;

class Auth extends BaseModel
{
    protected function attemptLogin($username, $password)
    {
        $sql = "SELECT id, password
                FROM users
                WHERE username = ? AND is_active = 1
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }

        return (int) $user['id'];
    }
}
