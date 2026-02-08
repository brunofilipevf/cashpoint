<?php

namespace Services;

class Model
{
    public function __get($name)
    {
        if ($name === 'db') {
            return Database::getInstance();
        }

        return null;
    }
}
