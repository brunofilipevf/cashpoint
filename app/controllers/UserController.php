<?php

namespace App\Controllers;

use Services\BaseController;
use App\Models\User;

class UserController extends BaseController
{
    public function index()
    {
        $data['users'] = User::findAll();
        render('users/index', $data);
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
