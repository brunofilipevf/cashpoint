<?php

namespace Services;

class Validator
{
    private $errors = [];

    public function field($value, $label, $patterns)
    {
        $rules = explode('|', $patterns);
        $initialCount = count($this->errors);

        foreach ($rules as $rule) {
            $parts = explode(':', $rule, 2);
            $method = 'validate' . ucfirst($parts[0]);

            if (isset($parts[1])) {
                $this->$method($value, $label, $parts[1]);
            } else {
                $this->$method($value, $label);
            }

            if (count($this->errors) > $initialCount) {
                break;
            }
        }

        return $this;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    private function validateRequired($value, $label)
    {
        if ($value === null) {
            $this->errors[] = "O campo {$label} é obrigatório";
        }
    }

    private function validateUsername($value, $label)
    {
        if ($value === null) {
            return;
        }

        if (!preg_match('/^[a-z0-9]+$/', $value)) {
            $this->errors[] = "O campo {$label} deve conter apenas letras minúsculas e números";
        }
    }

    private function validatePassword($value, $label)
    {
        if ($value === null) {
            return;
        }

        if (!preg_match('/^[a-zA-Z0-9]+$/', $value)) {
            $this->errors[] = "O campo {$label} deve conter apenas letras e números";
        }
    }

    private function validateText($value, $label)
    {
        if ($value === null) {
            return;
        }

        if ($value !== strip_tags($value) || !preg_match('/^[\p{L}\p{N}\p{P}\p{Zs}]+$/u', $value)) {
            $this->errors[] = "O campo {$label} contém caracteres inválidos";
        }
    }

    private function validateInt($value, $label)
    {
        if ($value === null) {
            return;
        }

        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->errors[] = "O campo {$label} deve ser um número inteiro";
        }
    }

    private function validateDecimal($value, $label)
    {
        if ($value === null) {
            return;
        }

        if (filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
            $this->errors[] = "O campo {$label} deve ser um número decimal";
        }
    }

    private function validateMinValue($value, $label, $min)
    {
        if ($value === null) {
            return;
        }

        if ($value < $min) {
            $this->errors[] = "O campo {$label} deve ser no mínimo {$min}";
        }
    }

    private function validateMaxValue($value, $label, $max)
    {
        if ($value === null) {
            return;
        }

        if ($value > $max) {
            $this->errors[] = "O campo {$label} deve ser no máximo {$max}";
        }
    }

    private function validateMinLength($value, $label, $min)
    {
        if ($value === null) {
            return;
        }

        if (mb_strlen($value, 'UTF-8') < $min) {
            $this->errors[] = "O campo {$label} deve ter no mínimo {$min} caracteres";
        }
    }

    private function validateMaxLength($value, $label, $max)
    {
        if ($value === null) {
            return;
        }

        if (mb_strlen($value, 'UTF-8') > $max) {
            $this->errors[] = "O campo {$label} deve ter no máximo {$max} caracteres";
        }
    }

    private function validateExactLength($value, $label, $length)
    {
        if ($value === null) {
            return;
        }

        $size = mb_strlen($value, 'UTF-8');
        $allowed = array_map('intval', explode(',', $length));
        $allowedList = implode(' ou ', $allowed);

        if (!in_array($size, $allowed, true)) {
            $this->errors[] = "O campo {$label} deve ter {$allowedList} caracteres";
        }
    }

    private function validateIn($value, $label, $content)
    {
        if ($value === null) {
            return;
        }

        $allowed = explode(',', $content);

        if (!in_array($value, $allowed, true)) {
            $this->errors[] = "O campo {$label} possui valor inválido";
        }
    }

    private function validateDate($value, $label)
    {
        if ($value === null) {
            return;
        }

        foreach (['Y-m-d', 'Y-m-d H:i:s'] as $format) {
            $date = \DateTime::createFromFormat($format, $value);

            if ($date && $date->format($format) === $value) {
                return;
            }
        }

        $this->errors[] = "O campo {$label} deve ser uma data válida";
    }
}
