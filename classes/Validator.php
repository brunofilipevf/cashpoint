<?php

class Validator
{
    private $name;
    private $value;
    private $errors = [];

    public function field($value, $name)
    {
        $this->name = $name;
        $this->value = $value;
        return $this;
    }

    public function rules($rules)
    {
        $ruleArray = explode('|', $rules);

        foreach ($ruleArray as $rule) {
            $ruleParts = explode(':', $rule);
            $ruleName = $ruleParts[0];
            $ruleParam = isset($ruleParts[1]) ? $ruleParts[1] : null;

            $method = 'validate' . ucfirst($ruleName);

            if (method_exists($this, $method)) {
                $this->$method($this->value, $ruleParam);
            }
        }

        return $this;
    }

    private function validateRequired($value)
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->errors[] = "O campo '{$this->name}' é obrigatório.";
        }
    }

    private function validateString($value)
    {
        if ($value !== null && $value !== '') {
            if (!is_string($value)) {
                $this->errors[] = "O campo '{$this->name}' deve ser uma string.";
            } else {
                if (strlen($value) > 10000) {
                    $this->errors[] = "O campo '{$this->name}' é muito longo.";
                }
            }
        }
    }

    private function validateNumeric($value)
    {
        if ($value !== null && $value !== '') {
            if (!is_numeric($value)) {
                $this->errors[] = "O campo '{$this->name}' deve ser um valor numérico.";
            }
        }
    }

    private function validateInteger($value)
    {
        if ($value !== null && $value !== '') {
            if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                $this->errors[] = "O campo '{$this->name}' deve ser um número inteiro.";
            }
        }
    }

    private function validateDecimal($value)
    {
        if ($value !== null && $value !== '') {
            if (filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
                $this->errors[] = "O campo '{$this->name}' deve ser um número decimal.";
            }
        }
    }

    private function validateDate($value)
    {
        if ($value !== null && $value !== '') {
            $dt = DateTime::createFromFormat('Y-m-d', $value);
            if (!$dt || $dt->format('Y-m-d') !== $value) {
                $this->errors[] = "O campo '{$this->name}' deve ser uma data válida no formato Y-m-d.";
            }
        }
    }

    private function validateIn($value, $param)
    {
        if ($value !== null && $value !== '') {
            $params = explode(',', $param);
            if (!in_array($value, $params, true)) {
                $this->errors[] = "O valor para o campo '{$this->name}' não é válido.";
            }
        }
    }

    private function validateMin($value, $param)
    {
        if ($value !== null && $value !== '') {
            $min = (float) $param;
            if (is_numeric($value)) {
                if ((float) $value < $min) {
                    $this->errors[] = "O campo '{$this->name}' deve ser maior ou igual a {$min}.";
                }
            } elseif (is_string($value)) {
                if (mb_strlen($value, 'UTF-8') < $min) {
                    $this->errors[] = "O campo '{$this->name}' deve ter no mínimo {$min} caracteres.";
                }
            }
        }
    }

    private function validateMax($value, $param)
    {
        if ($value !== null && $value !== '') {
            $max = (float) $param;
            if (is_numeric($value)) {
                if ((float) $value > $max) {
                    $this->errors[] = "O campo '{$this->name}' deve ser menor ou igual a {$max}.";
                }
            } elseif (is_string($value)) {
                if (mb_strlen($value, 'UTF-8') > $max) {
                    $this->errors[] = "O campo '{$this->name}' deve ter no máximo {$max} caracteres.";
                }
            }
        }
    }

    private function validateLength($value, $param)
    {
        if ($value !== null && $value !== '') {
            $len = mb_strlen($value, 'UTF-8');
            $params = explode(',', $param);
            if (!in_array($len, $params, true)) {
                $this->errors[] = "O campo '{$this->name}' deve ter o comprimento exato de um dos seguintes valores: " . implode(', ', $params) . ".";
            }
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
}