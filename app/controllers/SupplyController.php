<?php

namespace App\Controllers;

use App\Models\Supply;
use Core\Response;

class SupplyController
{
    public static function all()
    {
        Response::view('supply/index', [
            'supplies' => Supply::all()
        ]);
    }

    public static function show($supplyId)
    {
        $supplyData = Supply::get($supplyId);

        if (!$supplyData) {
            Response::abort(404);
        }

        Response::view('supply/edit', [
            'supply' => $supplyData
        ]);
    }
}
