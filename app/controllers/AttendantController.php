<?php

namespace App\Controllers;

use App\Models\Attendant;

class AttendantController
{
    public static function index()
    {
        Response::view('attendant/index', [
            'attendants' => Attendant::all()
        ]);
    }

    public static function edit($attendantId)
    {
        $attendantData = Attendant::get($attendantId);

        if (!$attendantData) {
            Response::abort(404);
        }

        Response::view('attendant/edit', [
            'attendant' => $attendantData
        ]);
    }

    public static function update($attendantId)
    {
        $requestData = [
            'fullname' => Request::input('fullname')
        ];

        $errors = Validator::fields($requestData, [
            'fullname' => 'string|max:60'
        ], [
            'fullname' => 'nome completo'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::redirect('same_uri');
        }

        Attendant::update($requestData, $attendantId);
        Session::setFlash('success', 'Frentista atualizado com sucesso');
        Response::redirect('/attendants');
    }
}
