<?php

namespace Core;

class Database
{
    private $connection = null;

    private function getConnection()
    {
        if ($this->connection === null) {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHAR;

            $this->connection = new \PDO($dsn, DB_USER, DB_PASS, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false
            ]);

            $this->connection->exec("SET time_zone = '" . date('P') . "'");
        }

        return $this->connection;
    }

    public function selectAll($sql, $param = [])
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($param);

        return $stmt->fetchAll();
    }

    public function selectOne($sql, $param = [])
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($param);

        return $stmt->fetch();
    }

    public function insert($sql, $param = [])
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($param);

        if ($stmt->rowCount() > 0) {
            return (int) $this->getConnection()->lastInsertId();
        }

        $this->rollBack();

        throw new \PDOException('Erro ao inserir registro, nenhuma linha foi afetada');
    }

    public function update($sql, $param = [])
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($param);

        return $stmt->rowCount() > 0;
    }

    public function delete($sql, $param = [])
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($param);

        return $stmt->rowCount() > 0;
    }

    public function count($sql, $param = [])
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($param);

        return (int) $stmt->fetchColumn();
    }

    public function exist($sql, $param = [])
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($param);

        return $stmt->fetchColumn() > 0;
    }

    public function existsInTables($id, $column, $tables = [])
    {
        foreach ($tables as $table) {
            $sql = "SELECT 1 FROM `{$table}` WHERE {$column} = ? LIMIT 1";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([$id]);

            if ($stmt->fetchColumn() > 0) {
                return true;
            }
        }

        return false;
    }

    public function beginTransaction()
    {
        if (!$this->getConnection()->inTransaction()) {
            $this->getConnection()->beginTransaction();
        }
    }

    public function commit()
    {
        if ($this->getConnection()->inTransaction()) {
            $this->getConnection()->commit();
        }
    }

    public function rollBack()
    {
        if ($this->getConnection()->inTransaction()) {
            $this->getConnection()->rollBack();
        }
    }
}
