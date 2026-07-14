<?php

namespace App\Controllers;

class AwardController
{
    public function __construct(
        private \App\Models\Award $award,
        private \App\Models\Group $group,
        private \App\Models\Product $product,
        private \App\Models\Redemption $redemption,
        private \Core\Database $database,
        private \Core\Request $request,
        private \Core\Response $response,
        private \Core\Session $session,
        private \Core\Validator $validator
    ) {}

    public function index()
    {
        $this->response->view('award/index', [
            'awards' => $this->award->all()
        ]);
    }

    public function add()
    {
        $this->response->view('award/add', [
            'products' => $this->product->all(),
            'groups' => $this->group->all()
        ]);
    }

    public function insert()
    {
        $requestData = [
            'name' => $this->request->post('name'),
            'product_id' => $this->request->post('product_id'),
            'required_points' => $this->request->post('required_points'),
            'max_redemption_total' => $this->request->post('max_redemption_total'),
            'max_redemption_per_customer' => $this->request->post('max_redemption_per_customer'),
            'group_id' => $this->request->post('group_id'),
            'start_date' => $this->request->post('start_date'),
            'end_date' => $this->request->post('end_date')
        ];

        $errors = $this->validator->fields($requestData, [
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
            $this->session->setFlash('danger', $errors);
            $this->response->redirect('same_uri');
        }

        if ($requestData['max_redemption_per_customer'] > $requestData['max_redemption_total']) {
            $this->session->setFlash('danger', 'O limite de resgate por cliente não pode ser maior que o limite total de resgate');
            $this->response->redirect('same_uri');
        }

        if ($requestData['end_date'] < $requestData['start_date']) {
            $this->session->setFlash('danger', 'A data de término não pode ser anterior à data de início');
            $this->response->redirect('same_uri');
        }

        $requestData['start_date'] .= ' 00:00:00';
        $requestData['end_date'] .= ' 23:59:59';

        $this->award->insert($requestData);
        $this->session->setFlash('success', 'Premiação adicionada com sucesso');
        $this->response->redirect('/awards');
    }

    public function edit($awardId)
    {
        $awardData = $this->award->find($awardId);

        if (!$awardData) {
            $this->response->abort(404);
        }

        $this->response->view('award/edit', [
            'award' => $awardData,
            'products' => $this->product->all(),
            'groups' => $this->group->all()
        ]);
    }

    public function update($awardId)
    {
        $awardData = $this->award->find($awardId);

        if (!$awardData) {
            $this->response->abort(404);
        }

        $requestData = [
            'name' => $this->request->post('name'),
            'product_id' => $this->request->post('product_id'),
            'required_points' => $this->request->post('required_points'),
            'max_redemption_total' => $this->request->post('max_redemption_total'),
            'max_redemption_per_customer' => $this->request->post('max_redemption_per_customer'),
            'group_id' => $this->request->post('group_id'),
            'start_date' => $this->request->post('start_date'),
            'end_date' => $this->request->post('end_date'),
            'is_active' => $this->request->post('is_active')
        ];

        $errors = $this->validator->fields($requestData, [
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
            $this->session->setFlash('danger', $errors);
            $this->response->redirect('same_uri');
        }

        if ($requestData['max_redemption_per_customer'] > $requestData['max_redemption_total']) {
            $this->session->setFlash('danger', 'O limite de resgate por cliente não pode ser maior que o limite total de resgate');
            $this->response->redirect('same_uri');
        }

        if ($requestData['end_date'] < $requestData['start_date']) {
            $this->session->setFlash('danger', 'A data de término não pode ser anterior à data de início');
            $this->response->redirect('same_uri');
        }

        $requestData['start_date'] .= ' 00:00:00';
        $requestData['end_date'] .= ' 23:59:59';

        if ($this->redemption->countByAward($awardData['id']) > 0) {
            $requestData['name'] = $awardData['name'];
            $requestData['product_id'] = $awardData['product_id'];
            $requestData['required_points'] = $awardData['required_points'];
            $requestData['group_id'] = $awardData['group_id'];
        }

        $this->award->update($requestData, $awardId);
        $this->session->setFlash('success', 'Premiação atualizada com sucesso');
        $this->response->redirect('/awards');
    }

    public function delete($awardId)
    {
        $awardData = $this->award->find($awardId);

        if (!$awardData) {
            $this->response->abort(404);
        }

        if ($this->database->existsInTables($awardId, 'award_id', ['redemption'])) {
            $this->session->setFlash('danger', 'Não é possível excluir esta premiação');
            $this->response->redirect('/awards/edit/' . $awardId);
        }

        $this->award->delete($awardId);
        $this->session->setFlash('success', 'Premiação excluída com sucesso');
        $this->response->redirect('/awards');
    }
}
