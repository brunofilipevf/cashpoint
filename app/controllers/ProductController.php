<?php

namespace App\Controllers;

use App\Models\Product;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class ProductController
{
    public function __construct(
        private Product $product,
        private Response $response,
        private Request $request,
        private Session $session,
        private Validator $validator
    ) { }

    public function index()
    {
        $products = $this->product->all();
        return $this->response->render('product/index', ['products' => $products]);
    }

    public function add()
    {
        return $this->response->render('product/add');
    }

    public function insert()
    {
        $data = [
            'name' => $this->request->input('name'),
            'barcode' => $this->request->input('barcode'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $rules = [
            'name' => 'required|string|min:2|max:60',
            'barcode' => 'required|string|max:13|unique:product,barcode'
        ];

        $labels = [
            'name' => 'nome',
            'barcode' => 'código de barras'
        ];

        $errors = $this->validator->fields($data, $rules, $labels);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            return $this->response->previous();
        }

        $inserted = $this->product->insert($data);

        if (!$inserted) {
            $this->session->setFlash('danger', 'Erro ao adicionar registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro adicionado com sucesso');
        return $this->response->redirect('/products');
    }

    public function edit($id)
    {
        $targetProduct = $this->product->get($id);

        if (!$targetProduct) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        return $this->response->render('product/edit', ['product' => $targetProduct]);
    }

    public function update($id)
    {
        $targetProduct = $this->product->get($id);

        if (!$targetProduct) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        $data = [
            'name' => $this->request->input('name'),
            'barcode' => $this->request->input('barcode'),
            'is_active' => $this->request->input('is_active'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $rules = [
            'name' => 'required|string|min:2|max:60',
            'barcode' => "required|string|max:13|unique:product,barcode,{$id}",
            'is_active' => 'required|in:0,1'
        ];

        $labels = [
            'name' => 'nome',
            'barcode' => 'código de barras',
            'is_active' => 'status'
        ];

        $errors = $this->validator->fields($data, $rules, $labels);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            return $this->response->previous();
        }

        $updated = $this->product->update($data, $id);

        if (!$updated) {
            $this->session->setFlash('danger', 'Erro ao atualizar registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro atualizado com sucesso');
        return $this->response->redirect('/products');
    }

    public function delete($id)
    {
        $targetProduct = $this->product->get($id);

        if (!$targetProduct) {
            $this->session->setFlash('danger', 'Registro não encontrado');
            return $this->response->previous();
        }

        $deleted = $this->product->delete($id);

        if ($deleted === '23000') {
            $this->session->setFlash('danger', 'Registro possui vínculos e não pode ser excluído');
            return $this->response->previous();
        }

        if (!$deleted) {
            $this->session->setFlash('danger', 'Erro ao excluir registro');
            return $this->response->previous();
        }

        $this->session->setFlash('success', 'Registro excluído com sucesso');
        return $this->response->redirect('/products');
    }
}
