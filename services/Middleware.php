<?php

namespace Services;

class Middleware
{
    public function __get($name)
    {
        if ($name === 'request') {
            return Request::getInstance();
        }

        if ($name === 'response') {
            return Response::getInstance();
        }

        if ($name === 'session') {
            return Session::getInstance();
        }

        return null;
    }
}
