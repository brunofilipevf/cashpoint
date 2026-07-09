<?php

namespace Core;

class Validator
{
    private static $errors = [];
    private static $isNumeric = false;

    public static function fields($values, $rules, $labels)
    {
        self::$errors = [];

        foreach ($rules as $field => $ruleset) {
            self::$isNumeric = false;

            $value = $values[$field];
            $label = $labels[$field];
            $array = explode('|', $ruleset);

            foreach ($array as $rule) {
                $parts = explode(':', $rule, 2);
                $name = $parts[0];

                if (isset($parts[1])) {
                    $param = $parts[1];
                } else {
                    $param = null;
                }

                $before = count(self::$errors);

                match ($name) {
                    'required' => self::validateRequired($value, $label),
                    'integer' => self::validateInteger($value, $label),
                    'numeric' => self::validateNumeric($value, $label),
                    'string' => self::validateString($value, $label),
                    'alpha' => self::validateAlpha($value, $label),
                    'alphanum' => self::validateAlphanum($value, $label),
                    'document' => self::validateDocument($value, $label),
                    'email' => self::validateEmail($value, $label),
                    'phone' => self::validatePhone($value, $label),
                    'date' => self::validateDate($value, $label),
                    'min' => self::validateMin($value, $label, $param),
                    'max' => self::validateMax($value, $label, $param),
                    'in' => self::validateIn($value, $label, $param),
                    'exist' => self::validateExist($value, $label, $param),
                    'unique' => self::validateUnique($value, $label, $param),
                    default => throw new \RuntimeException("[Validator] Validador não encontrado para '{$name}'")
                };

                if (count(self::$errors) > $before) {
                    break;
                }
            }
        }

        return self::$errors;
    }

    private static function validateRequired($value, $label)
    {
        if ($value === null || $value === '') {
            self::$errors[] = "O campo {$label} é obrigatório";
        }
    }

    private static function validateInteger($value, $label)
    {
        if ($value === null || $value === '') {
            return;
        }

        $intValue = filter_var($value, FILTER_VALIDATE_INT);

        if ($intValue === false) {
            self::$errors[] = "O campo {$label} deve ser um número inteiro";
            return;
        }

        if ($intValue < 0 || $intValue > 4294967295) {
            self::$errors[] = "O campo {$label} está fora da faixa permitida";
            return;
        }

        self::$isNumeric = true;
    }

    private static function validateNumeric($value, $label)
    {
        if ($value === null || $value === '') {
            return;
        }

        $floatValue = filter_var($value, FILTER_VALIDATE_FLOAT);

        if ($floatValue === false) {
            self::$errors[] = "O campo {$label} deve ser numérico";
            return;
        }

        if (stripos($value, 'e') !== false) {
            self::$errors[] = "O campo {$label} não aceita notação científica";
            return;
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $value)) {
            self::$errors[] = "O campo {$label} deve ter no máximo 2 casas decimais";
            return;
        }

        if ($floatValue < 0 || $floatValue > 99999999.99) {
            self::$errors[] = "O campo {$label} está fora da faixa permitida";
            return;
        }

        self::$isNumeric = true;
    }

    private static function validateString($value, $label)
    {
        if ($value === null || $value === '') {
            return;
        }

        if (mb_strlen($value, 'UTF-8') > 255) {
            self::$errors[] = "O campo {$label} está fora do limite permitido";
        }
    }

    private static function validateAlpha($value, $label)
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!ctype_alpha($value)) {
            self::$errors[] = "O campo {$label} deve conter apenas letras (sem acentos)";
            return;
        }

        if (mb_strlen($value, 'UTF-8') > 255) {
            self::$errors[] = "O campo {$label} está fora do limite permitido";
        }
    }

    private static function validateAlphanum($value, $label)
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!ctype_alnum($value)) {
            self::$errors[] = "O campo {$label} deve conter apenas letras (sem acentos) e números";
            return;
        }

        if (mb_strlen($value, 'UTF-8') > 255) {
            self::$errors[] = "O campo {$label} está fora do limite permitido";
        }
    }

    private static function validateDocument($value, $label)
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!ctype_digit($value)) {
            self::$errors[] = "O campo {$label} deve conter apenas números";
            return;
        }

        $len = mb_strlen($value, 'UTF-8');

        if ($len !== 11 && $len !== 14) {
            self::$errors[] = "O campo {$label} deve ter 11 ou 14 dígitos";
        }
    }

    private static function validateEmail($value, $label)
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            self::$errors[] = "O campo {$label} deve ser um e-mail válido";
            return;
        }

        if (mb_strlen($value, 'UTF-8') > 254) {
            self::$errors[] = "O campo {$label} está fora do limite permitido";
        }
    }

    private static function validatePhone($value, $label)
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!ctype_digit($value)) {
            self::$errors[] = "O campo {$label} deve conter apenas números";
            return;
        }

        if (mb_strlen($value, 'UTF-8') !== 11) {
            self::$errors[] = "O campo {$label} deve conter o DDD e o número, totalizando 11 dígitos";
        }
    }

    private static function validateDate($value, $label)
    {
        if ($value === null || $value === '') {
            return;
        }

        $date = \DateTime::createFromFormat('Y-m-d', $value);

        if ($date && $date->format('Y-m-d') === $value) {
            return;
        }

        self::$errors[] = "O campo {$label} deve ser uma data válida";
    }

    private static function validateMin($value, $label, $param)
    {
        if ($value === null || $value === '') {
            return;
        }

        if (self::$isNumeric) {
            if ((float) $value < (float) $param) {
                self::$errors[] = "O campo {$label} deve ser no mínimo {$param}";
            }
            return;
        }

        if (mb_strlen($value, 'UTF-8') < (int) $param) {
            self::$errors[] = "O campo {$label} deve ter no mínimo {$param} caracteres";
        }
    }

    private static function validateMax($value, $label, $param)
    {
        if ($value === null || $value === '') {
            return;
        }

        if (self::$isNumeric) {
            if ((float) $value > (float) $param) {
                self::$errors[] = "O campo {$label} deve ser no máximo {$param}";
            }
            return;
        }

        if (mb_strlen($value, 'UTF-8') > (int) $param) {
            self::$errors[] = "O campo {$label} deve ter no máximo {$param} caracteres";
        }
    }

    private static function validateIn($value, $label, $param)
    {
        if ($value === null || $value === '') {
            return;
        }

        $allowed = explode(',', $param);

        if (!in_array($value, $allowed, true)) {
            self::$errors[] = "O campo {$label} deve conter um valor válido";
        }
    }

    private static function validateExist($value, $label, $param)
    {
        if ($value === null || $value === '') {
            return;
        }

        $parts = explode(',', $param, 2);
        $table = $parts[0];
        $column = $parts[1];

        $sql = "SELECT 1 FROM `{$table}` WHERE {$column} = ? LIMIT 1";
        $result = Database::exist($sql, [$value]);

        if (!$result) {
            self::$errors[] = "O valor do campo {$label} não existe";
        }
    }

    private static function validateUnique($value, $label, $param)
    {
        if ($value === null || $value === '') {
            return;
        }

        $parts = explode(',', $param, 3);
        $table = $parts[0];
        $column = $parts[1];

        if (isset($parts[2])) {
            $excludeId = $parts[2];
        } else {
            $excludeId = null;
        }

        if ($excludeId) {
            $sql = "SELECT 1 FROM `{$table}` WHERE {$column} = ? AND id != ? LIMIT 1";
            $result = Database::exist($sql, [$value, $excludeId]);
        } else {
            $sql = "SELECT 1 FROM `{$table}` WHERE {$column} = ? LIMIT 1";
            $result = Database::exist($sql, [$value]);
        }

        if ($result) {
            self::$errors[] = "O valor do campo {$label} já está em uso";
        }
    }
}
