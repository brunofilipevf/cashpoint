<?php

namespace Services;

class BaseModel
{
    protected $db;

    public function __construct(Database $database)
    {
        $this->db = $database;
    }

    public static function __callStatic($method, $arguments)
    {
        $instance = Container::get(static::class);
        return $instance->$method(...$arguments);
    }
}
