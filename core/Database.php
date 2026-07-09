<?php

namespace Core;

class Database
{
    private static $connection = null;

    private static function getConnection()
    {
        if (self::$connection === null) {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHAR;

            self::$connection = new \PDO($dsn, DB_USER, DB_PASS, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false
            ]);

            self::$connection->exec("SET time_zone = '" . date('P') . "'");
        }

        return self::$connection;
    }

    public static function selectAll($sql, $param = [])
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($param);

        return $stmt->fetchAll();
    }

    public static function selectOne($sql, $param = [])
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($param);

        return $stmt->fetch();
    }

    public static function insert($sql, $param = [])
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($param);

        if ($stmt->rowCount() > 0) {
            return (int) self::getConnection()->lastInsertId();
        }

        return false;
    }

    public static function update($sql, $param = [])
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($param);

        return $stmt->rowCount() > 0;
    }

    public static function delete($sql, $param = [])
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($param);

        return $stmt->rowCount() > 0;
    }

    public static function count($sql, $param = [])
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($param);

        return (int) $stmt->fetchColumn();
    }

    public static function exist($sql, $param = [])
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($param);

        return $stmt->fetchColumn() > 0;
    }

    public static function existsInTables($id, $column, $tables = [])
    {
        foreach ($tables as $table) {
            $sql = "SELECT COUNT(id) FROM `{$table}` WHERE {$column} = ?";
            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute([$id]);

            if ($stmt->fetchColumn() > 0) {
                return true;
            }
        }

        return false;
    }

    public static function beginTransaction()
    {
        self::getConnection()->beginTransaction();
    }

    public static function commit()
    {
        self::getConnection()->commit();
    }

    public static function rollBack()
    {
        self::getConnection()->rollBack();
    }
}
