<?php

namespace App\Models;

use Core\Database;

class User
{
    public static function all()
    {
        // -------------------------------------------------------------------
        // Retorna todos os usuários sem o hash da senha
        // -------------------------------------------------------------------

        $sql = "SELECT u.*, l.name AS level_name, c.name AS company_name
                FROM `user` u
                INNER JOIN `level` l ON u.level_id = l.id
                LEFT JOIN `company` c ON u.company_id = c.id
                ORDER BY u.id DESC";
        $results = Database::selectAll($sql);

        foreach ($results as &$row) {
            unset($row['password']);
        }

        return $results;
    }

    public static function get($userId)
    {
        // -------------------------------------------------------------------
        // Busca um usuário pelo ID com sua hierarquia
        // -------------------------------------------------------------------

        $sql = "SELECT u.*, l.hierarchy
                FROM `user` u
                INNER JOIN `level` l ON u.level_id = l.id
                WHERE u.id = ?
                LIMIT 1";
        $result = Database::selectOne($sql, [$userId]);

        if ($result) {
            unset($result['password']);
        }

        return $result;
    }

    public static function insert($data)
    {
        // -------------------------------------------------------------------
        // Insere um novo usuário com senha criptografada
        // -------------------------------------------------------------------

        $data = self::hashPassword($data);
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `user` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";
        return Database::insert($sql, array_values($data));
    }

    public static function update($data, $userId)
    {
        // -------------------------------------------------------------------
        // Atualiza um usuário existente com senha criptografada
        // -------------------------------------------------------------------

        $data = self::hashPassword($data);
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE `user` SET {$set}, updated_at = NOW() WHERE id = ?";
        $params = array_values($data);
        $params[] = $userId;
        return Database::update($sql, $params);
    }

    public static function delete($userId)
    {
        // -------------------------------------------------------------------
        // Remove um usuário do banco pelo ID
        // -------------------------------------------------------------------

        $sql = "DELETE FROM `user` WHERE id = ?";
        return Database::delete($sql, [$userId]);
    }

    private static function hashPassword($data)
    {
        // -------------------------------------------------------------------
        // Criptografa a senha se fornecida, remove se vazia
        // -------------------------------------------------------------------

        if (isset($data['password']) && $data['password'] !== null && $data['password'] !== '') {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        return $data;
    }
}
