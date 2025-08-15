<?php

use App\Services\Redirect;
use App\Services\Request;
use App\Services\Session;
use App\Services\Validator;

function redirect()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new Redirect();
    }

    return $instance;
}

function request()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new Request();
    }

    return $instance;
}

function session()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new Session();
    }

    return $instance;
}

function validator()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new Validator();
    }

    return $instance;
}