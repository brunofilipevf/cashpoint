<?php

namespace App\Models;

use Core\Database;

class Score
{
    public static function all()
    {
        // -------------------------------------------------------------------
        // Retorna todas as pontuações com dados do cliente e usuário
        // -------------------------------------------------------------------

        $sql = "SELECT s.*, c.cpf, u.username,
                COALESCE(c.fullname, c.cpf) AS fullname_or_cpf
                FROM `score` s
                INNER JOIN `customer` c ON s.customer_id = c.id
                INNER JOIN `user` u ON s.user_id = u.id
                ORDER BY s.id DESC";
        return Database::selectAll($sql);
    }

    public static function insert($data)
    {
        // -------------------------------------------------------------------
        // Insere uma nova pontuação e retorna o ID gerado
        // -------------------------------------------------------------------

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `score` ({$columns}, created_at) VALUES ({$placeholders}, NOW())";
        return Database::insert($sql, array_values($data));
    }

    public static function countDailyByCustomer($customerId)
    {
        // -------------------------------------------------------------------
        // Conta pontuações do cliente na data atual
        // -------------------------------------------------------------------

        $sql = "SELECT COUNT(id) FROM `score` WHERE customer_id = ? AND DATE(created_at) = CURDATE()";
        return Database::count($sql, [$customerId]);
    }
}
