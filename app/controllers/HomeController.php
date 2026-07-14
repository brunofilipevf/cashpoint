<?php

namespace App\Controllers;

class HomeController
{
    public function __construct(
        private \Core\Response $response
    ) {}

    public function index()
    {
        $this->response->view('home/index');
    }
}
