<?php

namespace App\Services;

use DateTime;
use App\Services\Database;

class Validator
{
    private $messages = [];

    public function add($value, $name, $patterns = [])
    {
        if (mb_strlen((string) $value, 'UTF-8') > 9999) {
            $this->messages[] = "O campo {$name} não pode ter mais de 9999 caracteres.";
            return;
        }

        foreach ($patterns as $pattern) {
            $parts = explode(':', $pattern, 2);
            $methodName = 'validate' . ucfirst($parts[0]);
            $param = isset($parts[1]) ? $parts[1] : null;

            $error = $this->$methodName($value, $name, $param);
            if ($error !== null) {
                $this->messages[] = $error;
                break;
            }
        }
    }

    public function getMessages()
    {
        return $this->messages;
    }

    private function validateRequired($value, $name)
    {
        if ($value === null || $value === '') {
            return "O campo {$name} é obrigatório.";
        }
        return null;
    }

    private function validateString($value, $name)
    {
        if ($value !== null && $value !== '' && !is_string($value)) {
            return "O campo {$name} deve ser uma string.";
        }
        return null;
    }

    private function validateNumeric($value, $name)
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            return "O campo {$name} deve ser um valor numérico.";
        }
        return null;
    }

    private function validateInteger($value, $name)
    {
        if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
            return "O campo {$name} deve ser um valor inteiro.";
        }
        return null;
    }

    private function validateDecimal($value, $name)
    {
        if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
            return "O campo {$name} deve ser um valor decimal.";
        }
        return null;
    }

    private function validateDate($value, $name, $param)
    {
        if ($value !== null && $value !== '') {
            $d = DateTime::createFromFormat('Y-m-d', $value);
            if ($d && $d->format('Y-m-d') !== $value) {
                return "O campo {$name} deve ter o formato de data válido (Y-m-d).";
            }
        }
        return null;
    }

    private function validateMin($value, $name, $param)
    {
        if ($value !== null && $value !== '') {
            if (is_numeric($value)) {
                if ((float) $value < (float) $param) {
                    return "O campo {$name} deve ser no mínimo {$param}.";
                }
            } elseif (is_string($value)) {
                if (mb_strlen($value, 'UTF-8') < (int) $param) {
                    return "O campo {$name} deve ter no mínimo {$param} caracteres.";
                }
            }
        }
        return null;
    }

    private function validateMax($value, $name, $param)
    {
        if ($value !== null && $value !== '') {
            if (is_numeric($value)) {
                if ((float) $value > (float) $param) {
                    return "O campo {$name} deve ser no máximo {$param}.";
                }
            } elseif (is_string($value)) {
                if (mb_strlen($value, 'UTF-8') > (int) $param) {
                    return "O campo {$name} deve ter no máximo {$param} caracteres.";
                }
            }
        }
        return null;
    }

    private function validateLength($value, $name, $param)
    {
        if ($value !== null && $value !== '') {
            $lengths = array_map('intval', explode(',', $param));
            $valueLength = mb_strlen($value, 'UTF-8');
            if (!in_array($valueLength, $lengths)) {
                $lengths = implode(', ', $lengths);
                return "O campo {$name} deve ter um dos seguintes comprimentos: {$lengths}.";
            }
        }
        return null;
    }

    private function validateIn($value, $name, $param)
    {
        if ($value !== null && $value !== '') {
            $allowedValues = array_map('trim', explode(',', $param));
            if (!in_array($value, $allowedValues)) {
                $allowedValues  = implode(', ', $allowedValues );
                return "O campo {$name} deve conter um dos seguintes valores: {$allowedValues }.";
            }
        }
        return null;
    }

    private function validateExists($value, $name, $param)
    {
        if ($value !== null && $value !== '') {
            $parts = explode(',', $param, 2);

            $table = $parts[0];
            $column = $parts[1];

            $stmt = Database::prepare("SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = ?");
            $stmt->execute([$value]);

            if ($stmt->fetchColumn() === 0) {
                return "O valor informado para {$name} não existe.";
            }
        }
        return null;
    }

    private function validateUnique($value, $name, $param)
    {
        if ($value !== null && $value !== '') {
            $parts = explode(',', $param, 2);

            $table = $parts[0];
            $column = $parts[1];

            $stmt = Database::prepare("SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = ?");
            $stmt->execute([$value]);

            if ($stmt->fetchColumn() > 0) {
                return "O valor informado para {$name} já existe.";
            }
        }
        return null;
    }
}