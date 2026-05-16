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

    public function createSession($id, $token, $ip, $agent)
    {
        $now = date('Y-m-d H:i:s');
        $sql = "INSERT INTO `auth_session` (user_id, user_token, user_ip, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?)";
        return $this->db->insert($sql, [$id, $token, $ip, $agent, $now]);
    }

    public function validateSession($token)
    {
        $sql = "SELECT * FROM `auth_session` WHERE user_token = ? AND revoked_at IS NULL LIMIT 1";
        $session = $this->db->selectOne($sql, [$token]);

        if ($session === false) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $limit = 900;

        if ($session['updated_at'] !== null) {
            $lastActivity = $session['updated_at'];
        } else {
            $lastActivity = $session['created_at'];
        }

        $lastActivityTime = strtotime($lastActivity);
        $currentTime = strtotime($now);
        $difference = $currentTime - $lastActivityTime;

        if ($difference > $limit) {
            $this->revokeSession($token);
            return false;
        }

        $this->updateSession($token);
        return true;
    }

    public function updateSession($token)
    {
        $now = date('Y-m-d H:i:s');
        $sql = "UPDATE `auth_session` SET updated_at = ? WHERE user_token = ?";
        return $this->db->update($sql, [$token, $now]);
    }

    public function revokeSession($token)
    {
        $now = date('Y-m-d H:i:s');
        $sql = "UPDATE `auth_session` SET revoked_at = ? WHERE user_token = ? AND revoked_at IS NULL";
        return $this->db->update($sql, [$token, $now]);
    }
}
