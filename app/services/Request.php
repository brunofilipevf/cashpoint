<?php

namespace App\Services;

class Request
{
    public function input($key, $default = null)
    {
        if (isset($_POST[$key])) {
            $value = $_POST[$key];

            if (is_string($value)) {
                $value = trim($value);
            }

            return $value === '' ? null : $value;
        }
        return $default;
    }
}