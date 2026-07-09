<?php

namespace App\Models;

use Core\Database;

class Activity
{
    public static function create($userId, $ip)
    {
        // -------------------------------------------------------------------
        // Gera token aleatório e armazena hash no banco
        // -------------------------------------------------------------------

        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $sql = "INSERT INTO `activity` (user_id, token, user_ip, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())";

        if (!Database::insert($sql, [$userId, $hashedToken, $ip])) {
            return false;
        }

        return $token;
    }

    public static function verify($token)
    {
        // -------------------------------------------------------------------
        // Valida se o token existe, não foi revogado, está dentro
        // dos 15 minutos e o usuário está ativo. Se válido, renova
        // o timestamp de atividade.
        // -------------------------------------------------------------------

        $hashedToken = hash('sha256', $token);
        $sql = "SELECT a.user_id
                FROM `activity` a
                INNER JOIN `user` u ON a.user_id = u.id
                WHERE a.token = ?
                  AND a.revoked_at IS NULL
                  AND a.updated_at >= NOW() - INTERVAL 15 MINUTE
                  AND u.is_active = 1
                LIMIT 1";

        $activity = Database::selectOne($sql, [$hashedToken]);

        if (!$activity) {
            return false;
        }

        $sql = "UPDATE `activity` SET updated_at = NOW() WHERE token = ?";
        Database::update($sql, [$hashedToken]);

        return $activity['user_id'];
    }

    public static function revoke($token)
    {
        // -------------------------------------------------------------------
        // Revoga o token preenchendo a data de revogação
        // -------------------------------------------------------------------

        $hashedToken = hash('sha256', $token);
        $sql = "UPDATE `activity` SET revoked_at = NOW() WHERE token = ?";
        return Database::update($sql, [$hashedToken]);
    }
}
