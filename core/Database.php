<?php

namespace Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private $connection = null;

    private function getConnection()
    {
        if ($this->connection === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHAR;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        }

        return $this->connection;
    }

    public function prepare($sql)
    {
        return $this->getConnection()->prepare($sql);
    }

    public function lastInsertId()
    {
        return $this->getConnection()->lastInsertId();
    }

    public function beginTransaction()
    {
        return $this->getConnection()->beginTransaction();
    }

    public function commit()
    {
        return $this->getConnection()->commit();
    }

    public function rollBack()
    {
        return $this->getConnection()->rollBack();
    }

    public function select($sql, $params = [])
    {
        try {
            $stmt = $this->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new RuntimeException("Erro ao executar query: " . $e->getMessage());
        }
    }

    public function selectOne($sql, $params = [])
    {
        try {
            $stmt = $this->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new RuntimeException("Erro ao executar query: " . $e->getMessage());
        }
    }

    public function insert($sql, $params = [])
    {
        try {
            $stmt = $this->prepare($sql);
            $stmt->execute($params);
            $id = $this->lastInsertId();

            if ($id === false || $id === '0') {
                return false;
            }

            return (int) $id;
        } catch (PDOException $e) {
            throw new RuntimeException("Erro ao executar query: " . $e->getMessage());
        }
    }

    public function update($sql, $params = [])
    {
        try {
            $stmt = $this->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                return false;
            }

            return true;
        } catch (PDOException $e) {
            if ($e->getCode() === '45001') {
                return $e->getCode();
            }
            throw new RuntimeException("Erro ao executar query: " . $e->getMessage());
        }
    }

    public function delete($sql, $params = [])
    {
        try {
            $stmt = $this->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                return false;
            }

            return true;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return $e->getCode();
            }
            throw new RuntimeException("Erro ao executar query: " . $e->getMessage());
        }
    }
}
