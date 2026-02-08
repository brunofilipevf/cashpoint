<?php

namespace Services;

class Controller
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

        if ($name === 'validator') {
            return Validator::getInstance();
        }

        if ($name === 'view') {
            return View::getInstance();
        }

        return null;
    }

    protected function render($path, $data = [])
    {
        $data['flash'] = $this->session->getFlash();
        $data['csrf'] = $this->session->getCsrf();

        return $this->view->render($path, $data);
    }
}
