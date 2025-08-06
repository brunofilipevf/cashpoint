<?php

function redirector()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new Redirector();
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

function auth()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new Auth();
    }

    return $instance;
}