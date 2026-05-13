<?php

namespace App\Controllers;

use App\Models\Auth;
use Core\Response;

class HomeController
{
    public static function index()
    {
        Response::view('home/index');
    }
}
