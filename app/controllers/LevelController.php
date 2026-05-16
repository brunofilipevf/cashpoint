<?php

namespace App\Controllers;

use App\Models\Level;
use Core\Response;

class LevelController
{
    public function __construct(
        private Level $level,
        private Response $response
    ) { }

    public function index()
    {
        $levels = $this->level->all();
        return $this->response->render('level/index', ['levels' => $levels]);
    }
}
