<?php

namespace Services;

class BaseController
{
    protected $request;
    protected $response;
    protected $session;
    protected $validator;
    protected $view;

    public function __construct(Request $request, Response $response, Session $session, Validator $validator, View $view)
    {
        $this->request = $request;
        $this->response = $response;
        $this->session = $session;
        $this->validator = $validator;
        $this->view = $view;
    }
}
