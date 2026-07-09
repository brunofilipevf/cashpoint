<?php

namespace App\Controllers;

use Core\Response;

class HomeController
{
    public static function index()
    {
        Response::view('home/index');
    }
}
