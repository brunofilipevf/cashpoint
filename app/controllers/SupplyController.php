<?php

namespace App\Controllers;

use App\Models\{Auth, Supply};
use Core\Response;

class SupplyController
{
    public static function index()
    {
        $authUserData = Auth::stored();

        Response::view('supply/index', [
            'supplies' => Supply::all($authUserData['company_id'])
        ]);
    }

    public static function show($supplyId)
    {
        $supplyData = Supply::find($supplyId);

        if (!$supplyData) {
            Response::abort(404);
        }

        Response::view('supply/show', [
            'supply' => $supplyData
        ]);
    }
}
