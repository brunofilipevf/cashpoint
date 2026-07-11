<?php

namespace App\Models;

use Core\Database;

class Award
{
    public static function all()
    {
        // -------------------------------------------------------------------
        // Retorna todos os prêmios com nome do produto.
        // Se expirado, retorna is_active = 0 automaticamente.
        // -------------------------------------------------------------------

        $sql = "SELECT a.*, p.name AS product_name,
                    CASE
                        WHEN a.is_active = 1 AND a.end_date < NOW() THEN 0
                        ELSE a.is_active
                    END AS is_active
                FROM `award` a
                INNER JOIN `product` p ON a.product_id = p.id
                ORDER BY a.id DESC";
        return Database::selectAll($sql);
    }

    public static function allAvailable()
    {
        // -------------------------------------------------------------------
        // Retorna apenas prêmios ativos e dentro da vigência
        // -------------------------------------------------------------------

        $sql = "SELECT *
                FROM `award`
                WHERE start_date <= NOW()
                  AND end_date >= NOW()
                  AND is_active = 1
                ORDER BY id DESC";
        return Database::selectAll($sql);
    }

    public static function get($awardId)
    {
        // -------------------------------------------------------------------
        // Busca um prêmio pelo ID
        // -------------------------------------------------------------------

        $sql = "SELECT * FROM `award` WHERE id = ? LIMIT 1";
        return Database::selectOne($sql, [$awardId]);
    }

    public static function getForUpdate($awardId)
    {
        // -------------------------------------------------------------------
        // Busca um prêmio pelo ID com lock para transações
        // -------------------------------------------------------------------

        $sql = "SELECT * FROM `award` WHERE id = ? LIMIT 1 FOR UPDATE";
        return Database::selectOne($sql, [$awardId]);
    }

    public static function insert($data)
    {
        // -------------------------------------------------------------------
        // Insere um novo prêmio e retorna o ID gerado
        // -------------------------------------------------------------------

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `award` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";
        return Database::insert($sql, array_values($data));
    }

    public static function update($data, $awardId)
    {
        // -------------------------------------------------------------------
        // Atualiza um prêmio existente pelo ID
        // -------------------------------------------------------------------

        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE `award` SET {$set}, updated_at = NOW() WHERE id = ?";
        $params = array_values($data);
        $params[] = $awardId;
        return Database::update($sql, $params);
    }

    public static function delete($awardId)
    {
        // -------------------------------------------------------------------
        // Remove um prêmio do banco pelo ID
        // -------------------------------------------------------------------

        $sql = "DELETE FROM `award` WHERE id = ?";
        return Database::delete($sql, [$awardId]);
    }
}
