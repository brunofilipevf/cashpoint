<?php

namespace Core;

class Validator
{
    private $errors = [];
    private $isNumeric = false;

    public function __construct(
        private Database $database
    ) {}

    public function fields($values, $rules, $labels)
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleset) {
            $this->isNumeric = false;

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

                $before = count($this->errors);

                match ($name) {
                    'required' => $this->validateRequired($value, $label),
                    'integer' => $this->validateInteger($value, $label),
                    'numeric' => $this->validateNumeric($value, $label),
                    'string' => $this->validateString($value, $label),
                    'alpha' => $this->validateAlpha($value, $label),
                    'alphanum' => $this->validateAlphanum($value, $label),
                    'document' => $this->validateDocument($value, $label),
                    'email' => $this->validateEmail($value, $label),
                    'phone' => $this->validatePhone($value, $label),
                    'date' => $this->validateDate($value, $label),
                    'min' => $this->validateMin($value, $label, $param),
                    'max' => $this->validateMax($value, $label, $param),
                    'in' => $this->validateIn($value, $label, $param),
                    'exist' => $this->validateExist($value, $label, $param),
                    'unique' => $this->validateUnique($value, $label, $param),
                    default => throw new \RuntimeException("[Validator] Validador não encontrado para '{$name}'")
                };

                if (count($this->errors) > $before) {
                    break;
                }
            }
        }

        return $this->errors;
    }

    private function validateRequired($value, $label)
    {
        if ($value === null || $value === '') {
            $this->errors[] = "O campo {$label} é obrigatório";
        }
    }

    private function validateInteger($value, $label)
    {
        if ($value === null || $value === '') {
            return;
        }

        $intValue = filter_var($value, FILTER_VALIDATE_INT);

        if ($intValue === false) {
            $this->errors[] = "O campo {$label} deve ser um número inteiro";
            return;
        }

        if ($intValue < 0 || $intValue > 4294967295) {
            $this->errors[] = "O campo {$label} está fora da faixa permitida";
            return;
        }

        $this->isNumeric = true;
    }

    private function validateNumeric($value, $label)
    {
        if ($value === null || $value === '') {
            return;
        }

        $floatValue = filter_var($value, FILTER_VALIDATE_FLOAT);

        if ($floatValue === false) {
            $this->errors[] = "O campo {$label} deve ser numérico";
            return;
        }

        if (stripos($value, 'e') !== false) {
            $this->errors[] = "O campo {$label} não aceita notação científica";
            return;
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $value)) {
            $this->errors[] = "O campo {$label} deve ter no máximo 2 casas decimais";
            return;
        }

        if ($floatValue < 0 || $floatValue > 99999999.99) {
            $this->errors[] = "O campo {$label} está fora da faixa permitida";
            return;
        }

        $this->isNumeric = true;
    }

    private function validateString($value, $label)
    {
        if ($value === null || $value === '') {
            return;
        }

        if (mb_strlen($value, 'UTF-8') > 255) {
            $this->errors[] = "O campo {$label} está fora do limite permitido";
        }
    }

    private function validateAlpha($value, $label)
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!ctype_alpha($value)) {
            $this->errors[] = "O campo {$label} deve conter apenas letras (sem acentos)";
            return;
        }

        if (mb_strlen($value, 'UTF-8') > 255) {
            $this->errors[] = "O campo {$label} está fora do limite permitido";
        }
    }

    private function validateAlphanum($value, $label)
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!ctype_alnum($value)) {
            $this->errors[] = "O campo {$label} deve conter apenas letras (sem acentos) e números";
            return;
        }

        if (mb_strlen($value, 'UTF-8') > 255) {
            $this->errors[] = "O campo {$label} está fora do limite permitido";
        }
    }

    private function validateDocument($value, $label)
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!ctype_digit($value)) {
            $this->errors[] = "O campo {$label} deve conter apenas números";
            return;
        }

        $len = mb_strlen($value, 'UTF-8');

        if ($len !== 11 && $len !== 14) {
            $this->errors[] = "O campo {$label} deve ter 11 ou 14 dígitos";
        }
    }

    private function validateEmail($value, $label)
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "O campo {$label} deve ser um e-mail válido";
            return;
        }

        if (!str_contains($value, '.')) {
            $this->errors[] = "O campo {$label} deve ser um e-mail válido";
            return;
        }

        if (mb_strlen($value, 'UTF-8') > 254) {
            $this->errors[] = "O campo {$label} está fora do limite permitido";
        }
    }

    private function validatePhone($value, $label)
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!ctype_digit($value)) {
            $this->errors[] = "O campo {$label} deve conter apenas números";
            return;
        }

        if (mb_strlen($value, 'UTF-8') !== 11) {
            $this->errors[] = "O campo {$label} deve conter o DDD e o número, totalizando 11 dígitos";
        }
    }

    private function validateDate($value, $label)
    {
        if ($value === null || $value === '') {
            return;
        }

        $date = \DateTime::createFromFormat('Y-m-d', $value);

        if ($date && $date->format('Y-m-d') === $value) {
            return;
        }

        $this->errors[] = "O campo {$label} deve ser uma data válida";
    }

    private function validateMin($value, $label, $param)
    {
        if ($value === null || $value === '') {
            return;
        }

        if ($this->isNumeric) {
            if ((float) $value < (float) $param) {
                $this->errors[] = "O campo {$label} deve ser no mínimo {$param}";
            }
            return;
        }

        if (mb_strlen($value, 'UTF-8') < (int) $param) {
            $this->errors[] = "O campo {$label} deve ter no mínimo {$param} caracteres";
        }
    }

    private function validateMax($value, $label, $param)
    {
        if ($value === null || $value === '') {
            return;
        }

        if ($this->isNumeric) {
            if ((float) $value > (float) $param) {
                $this->errors[] = "O campo {$label} deve ser no máximo {$param}";
            }
            return;
        }

        if (mb_strlen($value, 'UTF-8') > (int) $param) {
            $this->errors[] = "O campo {$label} deve ter no máximo {$param} caracteres";
        }
    }

    private function validateIn($value, $label, $param)
    {
        if ($value === null || $value === '') {
            return;
        }

        $allowed = explode(',', $param);

        if (!in_array($value, $allowed, true)) {
            $this->errors[] = "O campo {$label} deve conter um valor válido";
        }
    }

    private function validateExist($value, $label, $param)
    {
        if ($value === null || $value === '') {
            return;
        }

        $parts = explode(',', $param, 2);
        $table = $parts[0];
        $column = $parts[1];

        $sql = "SELECT 1 FROM `{$table}` WHERE `{$column}` = ? LIMIT 1";
        $result = $this->database->exist($sql, [$value]);

        if (!$result) {
            $this->errors[] = "O valor do campo {$label} não existe";
        }
    }

    private function validateUnique($value, $label, $param)
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
            $result = $this->database->exist($sql, [$value, $excludeId]);
        } else {
            $sql = "SELECT 1 FROM `{$table}` WHERE {$column} = ? LIMIT 1";
            $result = $this->database->exist($sql, [$value]);
        }

        if ($result) {
            $this->errors[] = "O valor do campo {$label} já está em uso";
        }
    }
}
