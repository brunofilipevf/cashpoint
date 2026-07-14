<?php

namespace App\Models;

class Activity
{
    public function __construct(
        private \Core\Database $database
    ) {}

    public function create($userId, $ip)
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $sql = "INSERT INTO `activity` (user_id, token, ip, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";

        if (!$this->database->insert($sql, [$userId, $hashedToken, $ip])) {
            return false;
        }

        return $token;
    }

    public function verify($token)
    {
        $hashedToken = hash('sha256', $token);
        $sql = "SELECT a.user_id
                FROM `activity` a
                INNER JOIN `user` u ON a.user_id = u.id
                WHERE a.token = ?
                  AND a.revoked_at IS NULL
                  AND a.updated_at >= NOW() - INTERVAL 15 MINUTE
                  AND u.is_active = 1
                LIMIT 1";

        $activity = $this->database->selectOne($sql, [$hashedToken]);

        if (!$activity) {
            return false;
        }

        $sql = "UPDATE `activity` SET updated_at = NOW() WHERE token = ?";
        $this->database->update($sql, [$hashedToken]);

        return $activity['user_id'];
    }

    public function revoke($token)
    {
        $hashedToken = hash('sha256', $token);
        $sql = "UPDATE `activity` SET revoked_at = NOW() WHERE token = ?";

        return $this->database->update($sql, [$hashedToken]);
    }
}
