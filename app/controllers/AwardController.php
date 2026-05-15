<?php

namespace App\Controllers;

use App\Models\Award;
use App\Models\Product;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class AwardController
{
    public function __construct(
        private Award $award,
        private Product $product,
        private Response $response,
        private Request $request,
        private Session $session,
        private Validator $validator
    ) { }

    public function index()
    {
        $awards = $this->award->all();
        return $this->response->render('award/index', ['awards' => $awards]);
    }

    public function add()
    {
        $products = $this->product->all();
        return $this->response->render('award/add', ['products' => $products]);
    }

    public function insert()
    {
        $data = [
            'name' => $this->request->input('name'),
            'product_id' => $this->request->input('product_id'),
            'required_points' => $this->request->input('required_points'),
            'max_redemptions_total' => $this->request->input('max_redemptions_total'),
            'max_redemptions_per_customer' => $this->request->input('max_redemptions_per_customer'),
            'start_date' => $this->request->input('start_date'),
            'end_date' => $this->request->input('end_date'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $rules = [
            'name' => 'required|string|min:2|max:60',
            'product_id' => 'required|integer|exist:product,id',
            'required_points' => 'required|numeric|min:0.01',
            'max_redemptions_total' => 'required|integer|min:1',
            'max_redemptions_per_customer' => 'required|integer|min:1|before_or_equal:max_redemptions_total',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ];

        $labels = [
            'name' => 'nome',
            'product_id' => 'produto',
            'required_points' => 'pontos necessários',
            'max_redemptions_total' => 'limite total de resgates',
            'max_redemptions_per_customer' => 'limite de resgates por cliente',
            'start_date' => 'data de início',
            'end_date' => 'data de término'
        ];

        $errors = $this->validator->fields($data, $rules, $labels);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            return $this->response->previous();
        }

        $inserted = $this->award->insert($data);

        if (!$inserted) {
            $this->session->setFlash('danger', 'Erro ao adicionar registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro adicionado com sucesso');
        return $this->response->redirect('/awards');
    }

    public function edit($id)
    {
        $targetAward = $this->award->get($id);

        if (!$targetAward) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        $products = $this->product->all();
        return $this->response->render('award/edit', [
            'award' => $targetAward,
            'products' => $products
        ]);
    }

    public function update($id)
    {
        $targetAward = $this->award->get($id);

        if (!$targetAward) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        $data = [
            'name' => $this->request->input('name'),
            'product_id' => $this->request->input('product_id'),
            'required_points' => $this->request->input('required_points'),
            'max_redemptions_total' => $this->request->input('max_redemptions_total'),
            'max_redemptions_per_customer' => $this->request->input('max_redemptions_per_customer'),
            'start_date' => $this->request->input('start_date'),
            'end_date' => $this->request->input('end_date'),
            'is_active' => $this->request->input('is_active'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $rules = [
            'name' => 'required|string|min:2|max:60',
            'product_id' => 'required|integer|exist:product,id',
            'required_points' => 'required|numeric|min:0.01',
            'max_redemptions_total' => 'required|integer|min:1',
            'max_redemptions_per_customer' => 'required|integer|min:1|before_or_equal:max_redemptions_total',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'required|in:0,1'
        ];

        $labels = [
            'name' => 'nome',
            'product_id' => 'produto',
            'required_points' => 'pontos necessários',
            'max_redemptions_total' => 'limite total de resgates',
            'max_redemptions_per_customer' => 'limite de resgates por cliente',
            'start_date' => 'data de início',
            'end_date' => 'data de término',
            'is_active' => 'status'
        ];

        $errors = $this->validator->fields($data, $rules, $labels);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            return $this->response->previous();
        }

        $updated = $this->award->update($data, $id);

        if ($updated === '45001') {
            $this->session->setFlash('danger', 'Prêmio com resgates não pode ter o produto alterado');
            return $this->response->previous();
        }

        if (!$updated) {
            $this->session->setFlash('danger', 'Erro ao atualizar registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro atualizado com sucesso');
        return $this->response->redirect('/awards');
    }

    public function delete($id)
    {
        $targetAward = $this->award->get($id);

        if (!$targetAward) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        $deleted = $this->award->delete($id);

        if ($deleted === '23000') {
            $this->session->setFlash('danger', 'Registro possui vínculos e não pode ser excluído');
            return $this->response->previous();
        }

        if (!$deleted) {
            $this->session->setFlash('danger', 'Erro ao excluir registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro excluído com sucesso');
        return $this->response->redirect('/awards');
    }
}
