<?php

namespace App\Models;

use Core\Database;

class Auth
{
    private static $userData = [];

    public static function login($username, $password)
    {
        // -------------------------------------------------------------------
        // Busca usuário ativo e verifica a senha
        // -------------------------------------------------------------------

        $sql = "SELECT id, password FROM `user` WHERE username = ? AND is_active = 1 LIMIT 1";
        $user = Database::selectOne($sql, [$username]);

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        return $user['id'];
    }

    public static function store($data)
    {
        // -------------------------------------------------------------------
        // Armazena dados do usuário para a requisição atual
        // -------------------------------------------------------------------

        self::$userData = $data;
    }

    public static function stored()
    {
        // -------------------------------------------------------------------
        // Retorna os dados do usuário autenticado
        // -------------------------------------------------------------------

        return self::$userData;
    }
}
