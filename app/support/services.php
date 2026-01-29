<?php

use Services\Container;

function input($key, $default = null)
{
    return Container::get(Services\Request::class)->input($key, $default);
}

function flash($type, $message)
{
    Container::get(Services\Session::class)->setFlash($type, $message);
}

function validateCsrf($token)
{
    return Container::get(Services\Session::class)->validateCsrf($token);
}

function redirect($path, $statusCode = 302)
{
    Container::get(Services\Response::class)->redirectTo($path, $statusCode);
}

function back($statusCode = 302)
{
    Container::get(Services\Response::class)->redirectToPrevious($statusCode);
}

function render($path, $data = [])
{
    $data['flash'] = Container::get(Services\Session::class)->getFlash();
    $data['csrf'] = Container::get(Services\Session::class)->generateCsrf();

    $content = Container::get(Services\View::class)->render($path, $data);
    Container::get(Services\Response::class)->send($content);
}
