<?php

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        try {
            $this->pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHAR, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ]);
        } catch (PDOException $e) {
            error_log("Falha na conexão com o banco de dados: " . $e->getMessage());
            exit("Houve um problema ao conectar com o banco de dados. Tente novamente mais tarde.");
        }
    }

    private function __clone()
    {
        // Impede a clonagem da instância da classe.
    }

    public function __wakeup()
    {
        // Impede a desserialização da instância da classe.
    }

    private static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function __callStatic($method, $args)
    {
        $pdo = self::getInstance()->getConnection();
        return call_user_func_array([$pdo, $method], $args);
    }

    private function getConnection()
    {
        return $this->pdo;
    }
}