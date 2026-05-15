<?php

namespace App\Controllers;

use Core\Response;

class HomeController
{
    public function __construct(
        private Response $response
    ) { }

    public function index()
    {
        return $this->response->render('home/index');
    }
}
