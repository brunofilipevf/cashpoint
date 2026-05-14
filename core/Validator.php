<?php

namespace Core;

use DateTime;
use RuntimeException;

class Validator
{
    private static $errors = [];
    private static $values = [];
    private static $labels = [];
    private static $isNumeric = false;

    public static function fields($values, $rules, $labels)
    {
        self::$errors = [];
        self::$values = $values;
        self::$labels = $labels;

        foreach ($rules as $field => $fieldRules) {
            $value = self::$values[$field];
            $label = self::$labels[$field];
            $array = explode('|', $fieldRules);

            self::$isNumeric = false;
            self::validateField($value, $label, $array);
        }

        return self::$errors;
    }

    private static function validateField($value, $label, $rules)
    {
        foreach ($rules as $rule) {
            [$name, $param] = explode(':', $rule, 2) + [null, null];
            $before = count(self::$errors);

            match ($name) {
                'required' => self::validateRequired($value, $label, $param),
                'integer' => self::validateInteger($value, $label, $param),
                'numeric' => self::validateNumeric($value, $label, $param),
                'alpha' => self::validateAlpha($value, $label, $param),
                'alphanum' => self::validateAlphanum($value, $label, $param),
                'string' => self::validateString($value, $label, $param),
                'email' => self::validateEmail($value, $label, $param),
                'phone' => self::validatePhone($value, $label, $param),
                'document' => self::validateDocument($value, $label, $param),
                'date' => self::validateDate($value, $label, $param),
                'after_or_equal' => self::validateAfterOrEqual($value, $label, $param),
                'before_or_equal' => self::validateBeforeOrEqual($value, $label, $param),
                'in' => self::validateIn($value, $label, $param),
                'min' => self::validateMin($value, $label, $param),
                'max' => self::validateMax($value, $label, $param),
                'exist' => self::validateExist($value, $label, $param),
                'unique' => self::validateUnique($value, $label, $param),
                default => throw new RuntimeException("Regra de validação '{$name}' não encontrada")
            };

            if (count(self::$errors) > $before) {
                break;
            }
        }
    }

    private static function validateRequired($value, $label, $param)
    {
        if ($value === null || $value === '') {
            self::$errors[] = "O campo {$label} é obrigatório";
        }
    }

    private static function validateInteger($value, $label, $param)
    {
        if ($value === null || $value === '') {
            return;
        }

        self::$isNumeric = true;

        # Verifica se o valor corresponde a um número inteiro (positivo ou negativo)
        if (!preg_match('/^-?\d+$/', $value)) {
            self::$errors[] = "O campo {$label} deve ser um número inteiro";
            return;
        }

        # Verifica se o valor está dentro do intervalo permitido para inteiros sem sinal (0 a 4294967295)
        if ((int) $value < 0 || (int) $value > 4294967295) {
            self::$errors[] = "O campo {$label} deve estar entre 0 e 4294967295";
        }
    }

    private static function validateNumeric($value, $label, $param)
    {
        if ($value === null || $value === '') {
            return;
        }

        self::$isNumeric = true;

        # Verifica se o valor é numérico, permitindo até 2 casas decimais (formato monetário)
        if (!preg_match('/^-?\d+(?:\.\d{1,2})?$/', $value)) {
            self::$errors[] = "O campo {$label} deve ser numérico com até 2 casas decimais";
            return;
        }

        # Verifica se o valor está dentro do intervalo monetário permitido (0 a 99999999.99)
        if ((float) $value < 0 || (float) $value > 99999999.99) {
            self::$errors[] = "O campo {$label} deve estar entre 0 e 99999999.99";
        }
    }

    private static function validateAlpha($value, $label, $param)
    {
        if ($value === null || $value === '') {
            return;
        }

        # Verifica se o valor contém apenas letras do alfabeto (sem acentos, números ou caracteres especiais)
        if (!preg_match('/^[A-Za-z]+$/u', $value)) {
            self::$errors[] = "O campo {$label} deve conter apenas letras (sem acentos)";
            return;
        }

        $length = mb_strlen($value, 'UTF-8');

        # Verifica se o comprimento do texto não excede 255 caracteres
        if ($length > 255) {
            self::$errors[] = "O campo {$label} deve ter no máximo 255 caracteres";
        }
    }

    private static function validateAlphanum($value, $label, $param)
    {
        if ($value === null || $value === '') {
            return;
        }

        # Verifica se o valor contém apenas letras (sem acentos) e números
        if (!preg_match('/^[A-Za-z0-9]+$/u', $value)) {
            self::$errors[] = "O campo {$label} deve conter apenas letras (sem acentos) e números";
            return;
        }

        $length = mb_strlen($value, 'UTF-8');

        # Verifica se o comprimento do texto não excede 255 caracteres
        if ($length > 255) {
            self::$errors[] = "O campo {$label} deve ter no máximo 255 caracteres";
        }
    }

    private static function validateString($value, $label, $param)
    {
        if ($value === null || $value === '') {
            return;
        }

        $length = mb_strlen($value, 'UTF-8');

        # Verifica se o comprimento da string não excede 255 caracteres
        if ($length > 255) {
            self::$errors[] = "O campo {$label} deve ter no máximo 255 caracteres";
        }
    }

    private static function validateEmail($value, $label, $param)
    {
        if ($value === null || $value === '') {
            return;
        }

        # Verifica se o valor corresponde a um formato de e-mail válido usando filtro nativo do PHP
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            self::$errors[] = "O campo {$label} deve ser um e-mail válido";
            return;
        }

        $length = mb_strlen($value, 'UTF-8');

        # Verifica se o e-mail não excede 254 caracteres (limite máximo para endereços de e-mail segundo RFC)
        if ($length > 254) {
            self::$errors[] = "O campo {$label} deve ter no máximo 254 caracteres";
        }
    }

    private static function validatePhone($value, $label, $param)
    {
        if ($value === null || $value === '') {
            return;
        }

        # Verifica se o valor contém apenas dígitos numéricos
        if (!preg_match('/^[0-9]+$/', $value)) {
            self::$errors[] = "O campo {$label} deve conter apenas números";
            return;
        }

        $length = mb_strlen($value, 'UTF-8');

        # Verifica se o telefone tem exatamente 11 dígitos (formato brasileiro: DDD + número)
        if ($length !== 11) {
            self::$errors[] = "O campo {$label} deve conter o DDD e o número, totalizando 11 dígitos";
        }
    }

    private static function validateDocument($value, $label, $param)
    {
        if ($value === null || $value === '') {
            return;
        }

        # Verifica se o valor contém apenas dígitos numéricos
        if (!preg_match('/^[0-9]+$/', $value)) {
            self::$errors[] = "O campo {$label} deve conter apenas números";
            return;
        }

        $length = mb_strlen($value, 'UTF-8');

        # Verifica se o documento tem 11 dígitos (CPF) ou 14 dígitos (CNPJ)
        if ($length !== 11 && $length !== 14) {
            self::$errors[] = "O campo {$label} deve ter 11 ou 14 dígitos";
        }
    }

    private static function validateDate($value, $label, $param)
    {
        if ($value === null || $value === '') {
            return;
        }

        $date1 = DateTime::createFromFormat('Y-m-d', $value);

        # Verifica se o valor corresponde ao formato de data Y-m-d (ex: 2024-01-15)
        if ($date1 && $date1->format('Y-m-d') === $value) {
            return;
        }

        $date2 = DateTime::createFromFormat('Y-m-d H:i:s', $value);

        # Verifica se o valor corresponde ao formato de data e hora Y-m-d H:i:s (ex: 2024-01-15 14:30:00)
        if ($date2 && $date2->format('Y-m-d H:i:s') === $value) {
            return;
        }

        self::$errors[] = "O campo {$label} deve ser uma data válida";
    }

    private static function validateAfterOrEqual($value, $label, $param)
    {
        if ($param === null || $param === '') {
            self::$errors[] = "O campo {$label} não pôde ser validado corretamente";
            return;
        }

        if ($value === null || $value === '') {
            return;
        }

        $otherValue = self::$values[$param];

        # Se o campo de referência estiver vazio, não realiza a validação
        if ($otherValue === null) {
            return;
        }

        $otherLabel = self::$labels[$param];

        # Se o campo de referência já possui erros, não realiza a validação para evitar mensagens duplicadas
        foreach (self::$errors as $error) {
            if (str_contains($error, $otherLabel)) {
                return;
            }
        }

        # Se ambos os valores forem numéricos, converte para float para comparação numérica
        if (is_numeric($value) && is_numeric($otherValue)) {
            $value = (float) $value;
            $otherValue = (float) $otherValue;
        }

        # Verifica se o valor é maior ou igual ao campo de referência
        if ($value < $otherValue) {
            self::$errors[] = "O campo {$label} não pode ser inferior a {$otherLabel}";
        }
    }

    private static function validateBeforeOrEqual($value, $label, $param)
    {
        if ($param === null || $param === '') {
            self::$errors[] = "O campo {$label} não pôde ser validado corretamente";
            return;
        }

        if ($value === null || $value === '') {
            return;
        }

        $otherValue = self::$values[$param];

        # Se o campo de referência estiver vazio, não realiza a validação
        if ($otherValue === null) {
            return;
        }

        $otherLabel = self::$labels[$param];

        # Se o campo de referência já possui erros, não realiza a validação para evitar mensagens duplicadas
        foreach (self::$errors as $error) {
            if (str_contains($error, $otherLabel)) {
                return;
            }
        }

        # Se ambos os valores forem numéricos, converte para float para comparação numérica
        if (is_numeric($value) && is_numeric($otherValue)) {
            $value = (float) $value;
            $otherValue = (float) $otherValue;
        }

        # Verifica se o valor é menor ou igual ao campo de referência
        if ($value > $otherValue) {
            self::$errors[] = "O campo {$label} não pode ser superior a {$otherLabel}";
        }
    }

    private static function validateIn($value, $label, $param)
    {
        if ($param === null || $param === '') {
            self::$errors[] = "O campo {$label} não pôde ser validado corretamente";
            return;
        }

        if ($value === null || $value === '') {
            return;
        }

        $values = array_map('trim', explode(',', $param));

        # Verifica se o valor está presente na lista de valores permitidos
        if (!in_array($value, $values, true)) {
            self::$errors[] = "O campo {$label} deve conter um valor válido";
        }
    }

    private static function validateMin($value, $label, $param)
    {
        if ($param === null || $param === '') {
            self::$errors[] = "O campo {$label} não pôde ser validado corretamente";
            return;
        }

        if ($value === null || $value === '') {
            return;
        }

        # Se o campo for numérico, compara o valor numérico com o parâmetro mínimo
        if (self::$isNumeric) {
            if ((float) $value < (float) $param) {
                self::$errors[] = "O campo {$label} deve ser no mínimo {$param}";
            }
            return;
        }

        $length = mb_strlen($value, 'UTF-8');

        # Para campos não numéricos, verifica o comprimento mínimo da string
        if ((float) $length < (float) $param) {
            self::$errors[] = "O campo {$label} deve ter no mínimo {$param} caracteres";
        }
    }

    private static function validateMax($value, $label, $param)
    {
        if ($param === null || $param === '') {
            self::$errors[] = "O campo {$label} não pôde ser validado corretamente";
            return;
        }

        if ($value === null || $value === '') {
            return;
        }

        # Se o campo for numérico, compara o valor numérico com o parâmetro máximo
        if (self::$isNumeric) {
            if ((float) $value > (float) $param) {
                self::$errors[] = "O campo {$label} deve ser no máximo {$param}";
            }
            return;
        }

        $length = mb_strlen($value, 'UTF-8');

        # Para campos não numéricos, verifica o comprimento máximo da string
        if ((float) $length > (float) $param) {
            self::$errors[] = "O campo {$label} deve ter no máximo {$param} caracteres";
        }
    }

    private static function validateExist($value, $label, $param)
    {
        if ($param === null || $param === '') {
            self::$errors[] = "O campo {$label} não pôde ser validado corretamente";
            return;
        }

        if ($value === null || $value === '') {
            return;
        }

        [$table, $column] = explode(',', $param, 2);
        $sql = "SELECT 1 FROM `{$table}` WHERE {$column} = ? LIMIT 1";
        $result = Database::selectOne($sql, [$value]);

        # Verifica se o registro existe na tabela especificada
        if ($result === false) {
            self::$errors[] = "O valor do campo {$label} não existe";
        }
    }

    private static function validateUnique($value, $label, $param)
    {
        if ($param === null || $param === '') {
            self::$errors[] = "O campo {$label} não pôde ser validado corretamente";
            return;
        }

        if ($value === null || $value === '') {
            return;
        }

        [$table, $column, $excludeId] = explode(',', $param) + [null, null, null];
        $sql = "SELECT 1 FROM `{$table}` WHERE {$column} = ?";
        $params = [$value];

        # Se um ID for informado para exclusão, adiciona condição para ignorar o próprio registro (útil em edições)
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $sql .= " LIMIT 1";
        $result = Database::selectOne($sql, $params);

        # Verifica se já existe outro registro com o mesmo valor (unicidade)
        if ($result !== false) {
            self::$errors[] = "O valor do campo {$label} já está em uso";
        }
    }
}
