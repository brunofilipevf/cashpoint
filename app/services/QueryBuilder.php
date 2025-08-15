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
        $this->table = $this->sanitizeIdentifier($tableName);
        return $this;
    }

    public function select(...$columns)
    {
        foreach ($columns as $column) {
            $this->selects[] = $this->sanitizeIdentifier($column);
        }
        return $this;
    }

    public function leftJoin($joinTable, $first, $operator, $second = null)
    {
        if ($second === null) {
            $second = $operator;
            $operator = '=';
        }

        $joinTable = $this->sanitizeIdentifier($joinTable);
        $first = $this->sanitizeIdentifier($first);
        $second = $this->sanitizeIdentifier($second);
        $operator = $this->sanitizeOperator($operator);

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
            'column'   => $this->sanitizeIdentifier($column),
            'operator' => $this->sanitizeOperator($operator),
        ];

        $this->bindings[] = $value;

        return $this;
    }

    public function orderBy($column, $direction = 'asc')
    {
        $column = $this->sanitizeIdentifier($column);
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orderBy = "ORDER BY `{$column}` {$direction}";
        return $this;
    }

    public function limit($count)
    {
        $this->limit = "LIMIT " . (int) $count;
        return $this;
    }

    public function without(...$columns)
    {
        foreach ($columns as $column) {
            $this->without[] = $this->sanitizeIdentifier($column);
        }
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
        $columns = array_map([$this, 'sanitizeIdentifier'], array_keys($data));
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
        $updateBindings = array_values($data);

        foreach (array_keys($data) as $column) {
            $column = $this->sanitizeIdentifier($column);
            $setClauses[] = "`{$column}` = ?";
        }

        $sql = "UPDATE `{$this->table}` SET " . implode(", ", $setClauses);

        $allBindings = $updateBindings;
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWhereClauses();
            $allBindings = array_merge($updateBindings, $this->bindings);
        }

        $stmt = Database::prepare($sql);
        $stmt->execute($allBindings);

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
        if (empty($this->selects)) {
            $selects = '*';
        } else {
            $selects = '`' . implode('`, `', $this->selects) . '`';
        }

        $sql = "SELECT {$selects} FROM `{$this->table}`";

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

    private function sanitizeIdentifier($identifier)
    {
        $identifier = preg_replace('/[^a-zA-Z0-9_]/', '', $identifier);

        if (empty($identifier)) {
            return 'id';
        }

        if (is_numeric($identifier[0])) {
            $identifier = 'col_' . $identifier;
        }

        return $identifier;
    }

    private function sanitizeOperator($operator)
    {
        $allowedOperators = ['=', '!=', '<>', '>', '<', '>=', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'IS NULL', 'IS NOT NULL'];

        $operator = strtoupper(trim($operator));

        return in_array($operator, $allowedOperators) ? $operator : '=';
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