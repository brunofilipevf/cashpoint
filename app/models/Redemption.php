<?php

namespace App\Models;

use Core\Database;

class Redemption
{
    public static function all()
    {
        // -------------------------------------------------------------------
        // Retorna todos os resgates com dados do cliente, prêmio e usuário
        // -------------------------------------------------------------------

        $sql = "SELECT r.*, c.cpf, u.username, a.name AS award_name,
                COALESCE(c.fullname, c.cpf) AS fullname_or_cpf
                FROM `redemption` r
                INNER JOIN `customer` c ON r.customer_id = c.id
                INNER JOIN `award` a ON r.award_id = a.id
                INNER JOIN `user` u ON r.user_id = u.id
                ORDER BY r.id DESC";
        return Database::selectAll($sql);
    }

    public static function insert($data)
    {
        // -------------------------------------------------------------------
        // Insere uma nova pontuação e retorna o ID gerado
        // -------------------------------------------------------------------

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `redemption` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";
        return Database::insert($sql, array_values($data));
    }

    public static function countByAward($awardId)
    {
        // -------------------------------------------------------------------
        // Conta o total de resgates de um prêmio específico
        // -------------------------------------------------------------------

        $sql = "SELECT COUNT(id) FROM `redemption` WHERE award_id = ?";
        return Database::count($sql, [$awardId]);
    }

    public static function countByAwardAndCustomer($awardId, $customerId)
    {
        // -------------------------------------------------------------------
        // Conta os resgates de um cliente para um prêmio
        // -------------------------------------------------------------------

        $sql = "SELECT COUNT(id) FROM `redemption` WHERE award_id = ? AND customer_id = ?";
        return Database::count($sql, [$awardId, $customerId]);
    }
}
