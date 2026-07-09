<?php

namespace App\Controllers;

use App\Models\{Award, Group, Product, Redemption};
use Core\{Request, Response, Session, Validator};

class AwardController
{
    public static function index()
    {
        Response::view('award/index', [
            'awards' => Award::all()
        ]);
    }

    public static function add()
    {
        Response::view('award/add', [
            'products' => Product::all(),
            'groups' => Group::all()
        ]);
    }

    public static function insert()
    {
        $requestData = [
            'name' => Request::input('name'),
            'product_id' => Request::input('product_id'),
            'required_points' => Request::input('required_points'),
            'max_redemption_total' => Request::input('max_redemption_total'),
            'max_redemption_per_customer' => Request::input('max_redemption_per_customer'),
            'group_id' => Request::input('group_id'),
            'start_date' => Request::input('start_date'),
            'end_date' => Request::input('end_date')
        ];

        $errors = Validator::fields($requestData, [
            'name' => 'required|string|max:60',
            'product_id' => 'required|integer|exist:product,id',
            'required_points' => 'required|numeric|min:0.01',
            'max_redemption_total' => 'required|integer|min:1',
            'max_redemption_per_customer' => 'required|integer|min:1',
            'group_id' => 'integer|exist:group,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ], [
            'name' => 'nome',
            'product_id' => 'produto',
            'required_points' => 'pontos necessários',
            'max_redemption_total' => 'limite total de resgate',
            'max_redemption_per_customer' => 'limite de resgate por cliente',
            'group_id' => 'grupo exclusivo',
            'start_date' => 'data de início',
            'end_date' => 'data de término'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::redirect('same_uri');
        }

        if ($requestData['max_redemption_per_customer'] > $requestData['max_redemption_total']) {
            Session::setFlash('danger', 'O limite de resgate por cliente não pode ser maior que o limite total de resgate');
            Response::redirect('same_uri');
        }

        if ($requestData['end_date'] < $requestData['start_date']) {
            Session::setFlash('danger', 'A data de término não pode ser anterior à data de início');
            Response::redirect('same_uri');
        }

        $requestData['start_date'] .= ' 00:00:00';
        $requestData['end_date'] .= ' 23:59:59';

        Award::insert($requestData);
        Session::setFlash('success', 'Premiação adicionada com sucesso');
        Response::redirect('/awards');
    }

    public static function edit($awardId)
    {
        $awardData = Award::get($awardId);

        if (!$awardData) {
            Response::abort(404);
        }

        Response::view('award/edit', [
            'award' => $awardData,
            'products' => Product::all(),
            'groups' => Group::all()
        ]);
    }

    public static function update($awardId)
    {
        $awardData = Award::get($awardId);

        if (!$awardData) {
            Response::abort(404);
        }

        $requestData = [
            'name' => Request::input('name'),
            'product_id' => Request::input('product_id'),
            'required_points' => Request::input('required_points'),
            'max_redemption_total' => Request::input('max_redemption_total'),
            'max_redemption_per_customer' => Request::input('max_redemption_per_customer'),
            'group_id' => Request::input('group_id'),
            'start_date' => Request::input('start_date'),
            'end_date' => Request::input('end_date'),
            'is_active' => Request::input('is_active')
        ];

        $errors = Validator::fields($requestData, [
            'name' => 'required|string|max:60',
            'product_id' => 'required|integer|exist:product,id',
            'required_points' => 'required|numeric|min:0.01',
            'max_redemption_total' => 'required|integer|min:1',
            'max_redemption_per_customer' => 'required|integer|min:1',
            'group_id' => 'integer|exist:group,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'is_active' => 'required|in:0,1'
        ], [
            'name' => 'nome',
            'product_id' => 'produto',
            'required_points' => 'pontos necessários',
            'max_redemption_total' => 'limite total de resgate',
            'max_redemption_per_customer' => 'limite de resgate por cliente',
            'group_id' => 'grupo exclusivo',
            'start_date' => 'data de início',
            'end_date' => 'data de término',
            'is_active' => 'status'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::redirect('same_uri');
        }

        if ($requestData['max_redemption_per_customer'] > $requestData['max_redemption_total']) {
            Session::setFlash('danger', 'O limite de resgate por cliente não pode ser maior que o limite total de resgate');
            Response::redirect('same_uri');
        }

        if ($requestData['end_date'] < $requestData['start_date']) {
            Session::setFlash('danger', 'A data de término não pode ser anterior à data de início');
            Response::redirect('same_uri');
        }

        $requestData['start_date'] .= ' 00:00:00';
        $requestData['end_date'] .= ' 23:59:59';

        if (Redemption::countByAward($awardData['id']) > 0) {
            $requestData['name'] = $awardData['name'];
            $requestData['product_id'] = $awardData['product_id'];
            $requestData['required_points'] = $awardData['required_points'];
            $requestData['group_id'] = $awardData['group_id'];
        }

        Award::update($requestData, $awardId);
        Session::setFlash('success', 'Premiação atualizada com sucesso');
        Response::redirect('/awards');
    }

    public static function delete($awardId)
    {
        $awardData = Award::get($awardId);

        if (!$awardData) {
            Response::abort(404);
        }

        if (Database::existsInTables($awardId, 'award_id', ['redemption'])) {
            Session::setFlash('danger', 'Não é possível excluir esta premiação');
            Response::redirect('/awards/edit/' . $awardId);
        }

        Award::delete($awardId);
        Session::setFlash('success', 'Premiação excluída com sucesso');
        Response::redirect('/awards');
    }
}
