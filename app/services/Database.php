<?php

namespace App\Services;

use PDO;

class Database
{
    private $pdo;
    private static $instance;
    private static $allowedMethods = ['prepare', 'lastInsertId', 'beginTransaction', 'commit', 'rollback'];

    private function __construct()
    {
        $this->pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHAR, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ]);
    }

    public static function __callStatic($method, $args)
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        if (!in_array($method, self::$allowedMethods)) {
            return null;
        }

        return call_user_func_array([self::$instance->pdo, $method], $args);
    }
}