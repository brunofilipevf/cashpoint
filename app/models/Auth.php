<?php

namespace App\Models;

class Auth
{
    private $data = [];

    public function __construct(
        private \Core\Database $database
    ) {}

    public function login($username, $password)
    {
        $sql = "SELECT id, password FROM `user` WHERE username = ? AND is_active = 1 LIMIT 1";
        $user = $this->database->selectOne($sql, [$username]);

        if ($user) {
            $hash = $user['password'];
        } else {
            $hash = '$2y$10$WxROWWu.Xm4h9gRNy5wOjuEhet/hC0Nq7Vj9FoWn2/5m0hS6lP.KW';
        }

        if (!password_verify($password, $hash)) {
            return false;
        }

        if (!$user) {
            return false;
        }

        return $user['id'];
    }

    public function store($data)
    {
        $this->data = $data;
    }

    public function stored()
    {
        return $this->data;
    }
}
