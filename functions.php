<?php

/*
|--------------------------------------------------------------------------
| Função de escape para strings e arrays
|--------------------------------------------------------------------------
*/

function e($value)
{
    if ($value === null || $value === '') {
        return null;
    }

    if (is_array($value)) {
        return array_map('e', $value);
    }

    if (!is_string($value)) {
        return null;
    }

    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/*
|--------------------------------------------------------------------------
| Função auxiliar para a classe Redirector
|--------------------------------------------------------------------------
*/

function redirector()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new Redirector();
    }

    return $instance;
}

/*
|--------------------------------------------------------------------------
| Função auxiliar para a classe Request
|--------------------------------------------------------------------------
*/

function request()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new Request();
    }

    return $instance;
}

/*
|--------------------------------------------------------------------------
| Função auxiliar para a classe Session
|--------------------------------------------------------------------------
*/

function session()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new Session();
    }

    return $instance;
}

/*
|--------------------------------------------------------------------------
| Função auxiliar para a classe Auth
|--------------------------------------------------------------------------
*/

function auth()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new Auth();
    }

    return $instance;
}