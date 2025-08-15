<?php

namespace App\Services;

use App\Services\Database;

class QueryBuilder
{
    private $table;
    private $selects = [];
    private $joins = [];
    private $wheres = [];
    private $orderBy;
    private $limit;
    private $bindings = [];
    private $without = [];

    public function table($tableName)
    {
        $this->table = $tableName;
        return $this;
    }

    public function select(...$columns)
    {
        $this->selects = array_merge($this->selects, $columns);
        return $this;
    }

    public function leftJoin($joinTable, $first, $operator, $second = null)
    {
        if ($second === null) {
            $second = $operator;
            $operator = '=';
        }

        $this->joins[] = "LEFT JOIN `{$joinTable}` ON `{$this->table}`.`{$first}` {$operator} `{$joinTable}`.`{$second}`";

        return $this;
    }

    public function where($column, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'column'   => $column,
            'operator' => $operator,
        ];

        $this->bindings[] = $value;

        return $this;
    }

    public function orderBy($column, $direction = 'asc')
    {
        $this->orderBy = "ORDER BY `{$column}` " . strtoupper($direction);
        return $this;
    }

    public function limit($count)
    {
        $this->limit = "LIMIT " . (int) $count;
        return $this;
    }

    public function without(...$columns)
    {
        $this->without = array_merge($this->without, $columns);
        return $this;
    }

    public function first()
    {
        $results = $this->limit(1)->get();
        return $results[0] ?? null;
    }

    public function get()
    {
        $sql = $this->buildQuery();

        $stmt = Database::prepare($sql);
        $stmt->execute($this->bindings);

        $results = $stmt->fetchAll();

        if (!empty($this->without)) {
            foreach ($results as &$row) {
                foreach ($this->without as $column) {
                    unset($row[$column]);
                }
            }
        }

        $this->reset();

        return $results;
    }

    public function paginator($perPage = 15, $currentPage = 1)
    {
        $totalQuery = clone $this;
        $totalQuery->resetStateForCount();
        $totalItems = $totalQuery->count();

        $totalPages = ceil($totalItems / $perPage);

        $offset = ($currentPage - 1) * $perPage;

        $this->limit = "LIMIT {$perPage} OFFSET {$offset}";

        $items = $this->get();

        $this->reset();

        return [
            'items'       => $items,
            'totalItems'  => (int) $totalItems,
            'perPage'     => (int) $perPage,
            'currentPage' => (int) $currentPage,
            'totalPages'  => (int) $totalPages,
            'hasPrevious' => $currentPage > 1,
            'hasNext'     => $currentPage < $totalPages,
        ];
    }

    public function insert($data)
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        $bindings = array_values($data);

        $sql = "INSERT INTO `{$this->table}` (`" . implode("`, `", $columns) . "`) VALUES (" . implode(", ", $placeholders) . ")";

        $stmt = Database::prepare($sql);
        $stmt->execute($bindings);

        $this->reset();

        return Database::lastInsertId();
    }

    public function update($data)
    {
        $setClauses = [];
        $bindings = array_values($data);

        foreach (array_keys($data) as $column) {
            $setClauses[] = "`{$column}` = ?";
        }

        $sql = "UPDATE `{$this->table}` SET " . implode(", ", $setClauses);

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWhereClauses();
            $bindings = array_merge($bindings, $this->bindings);
        }

        $stmt = Database::prepare($sql);
        $stmt->execute($bindings);

        $this->reset();

        return $stmt->rowCount();
    }

    public function delete()
    {
        $sql = "DELETE FROM `{$this->table}`";

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWhereClauses();
        }

        $stmt = Database::prepare($sql);
        $stmt->execute($this->bindings);

        $this->reset();

        return $stmt->rowCount();
    }

    public function count()
    {
        $sql = $this->buildCountQuery();
        $stmt = Database::prepare($sql);
        $stmt->execute($this->bindings);

        return $stmt->fetchColumn();
    }

    private function buildQuery()
    {
        $selects = empty($this->selects) ? '*' : implode('`, `', $this->selects);
        $sql = "SELECT `{$selects}` FROM `{$this->table}`";

        if (!empty($this->joins)) {
            $sql .= " " . implode(" ", $this->joins);
        }

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWhereClauses();
        }

        if ($this->orderBy !== null) {
            $sql .= " {$this->orderBy}";
        }

        if ($this->limit !== null) {
            $sql .= " {$this->limit}";
        }

        return $sql;
    }

    private function buildCountQuery()
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";

        if (!empty($this->joins)) {
            $sql .= " " . implode(" ", $this->joins);
        }

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWhereClauses();
        }

        return $sql;
    }

    private function buildWhereClauses()
    {
        $whereClauses = [];
        foreach ($this->wheres as $where) {
            $whereClauses[] = "`{$where['column']}` {$where['operator']} ?";
        }
        return implode(' AND ', $whereClauses);
    }

    private function reset()
    {
        $this->table = null;
        $this->selects = [];
        $this->wheres = [];
        $this->joins = [];
        $this->orderBy = null;
        $this->limit = null;
        $this->bindings = [];
        $this->without = [];
    }

    private function resetStateForCount()
    {
        $this->orderBy = null;
        $this->limit = null;
        $this->selects = [];
    }
}