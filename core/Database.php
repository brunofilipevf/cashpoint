<?php

namespace Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static $connection = null;

    private static function getConnection()
    {
        if (self::$connection === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHAR;
            self::$connection = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        }

        return self::$connection;
    }

    public static function prepare($sql)
    {
        return self::getConnection()->prepare($sql);
    }

    public static function lastInsertId()
    {
        return self::getConnection()->lastInsertId();
    }

    public static function beginTransaction()
    {
        return self::getConnection()->beginTransaction();
    }

    public static function commit()
    {
        return self::getConnection()->commit();
    }

    public static function rollBack()
    {
        return self::getConnection()->rollBack();
    }

    public static function select($sql, $params = [])
    {
        try {
            $stmt = self::prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new RuntimeException("Erro ao executar consulta no banco de dados: " . $e->getMessage());
        }
    }

    public static function selectOne($sql, $params = [])
    {
        try {
            $stmt = self::prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();

            if ($result === false) {
                return false;
            }

            return $result;
        } catch (PDOException $e) {
            throw new RuntimeException("Erro ao executar consulta no banco de dados: " . $e->getMessage());
        }
    }

    public static function insert($sql, $params = [])
    {
        try {
            $stmt = self::prepare($sql);
            $stmt->execute($params);
            $id = self::lastInsertId();

            if ($id === false || $id === '0') {
                return false;
            }

            return (int) $id;
        } catch (PDOException $e) {
            throw new RuntimeException("Erro ao executar inserção no banco de dados: " . $e->getMessage());
        }
    }

    public static function update($sql, $params = [])
    {
        try {
            $stmt = self::prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                return false;
            }

            return true;
        } catch (PDOException $e) {
            if ($e->getCode() === '45001') {
                return false;
            }
            throw new RuntimeException("Erro ao executar atualização no banco de dados: " . $e->getMessage());
        }
    }

    public static function delete($sql, $params = [])
    {
        try {
            $stmt = self::prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                return false;
            }

            return true;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return false;
            }
            throw new RuntimeException("Erro ao executar exclusão no banco de dados: " . $e->getMessage());
        }
    }
}
