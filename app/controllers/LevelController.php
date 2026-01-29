<?php

namespace App\Controllers;

use Services\BaseController;
use App\Models\Level;

class LevelController extends BaseController
{
    public function index()
    {
        $data['levels'] = Level::findAll();
        render('levels/index', $data);
    }

    public function create()
    {
        //
    }

    public function store()
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update($id)
    {
        //
    }
}
