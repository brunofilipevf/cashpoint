<?php

namespace Services;

class BaseMiddleware
{
    protected $request;
    protected $response;
    protected $session;

    public function __construct(Request $request, Response $response, Session $session)
    {
        $this->request = $request;
        $this->response = $response;
        $this->session = $session;
    }
}
