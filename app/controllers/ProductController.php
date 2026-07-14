<?php

namespace App\Controllers;

class ProductController
{
    public function __construct(
        private \App\Models\Product $product,
        private \Core\Database $database,
        private \Core\Request $request,
        private \Core\Response $response,
        private \Core\Session $session,
        private \Core\Validator $validator
    ) {}

    public function index()
    {
        $this->response->view('product/index', [
            'products' => $this->product->all()
        ]);
    }

    public function add()
    {
        $this->response->view('product/add');
    }

    public function insert()
    {
        $requestData = [
            'name' => $this->request->post('name'),
            'barcode' => $this->request->post('barcode')
        ];

        $errors = $this->validator->fields($requestData, [
            'name' => 'required|string|max:60',
            'barcode' => 'required|string|max:13|unique:product,barcode'
        ], [
            'name' => 'nome',
            'barcode' => 'código de barras'
        ]);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            $this->response->redirect('same_uri');
        }

        $this->product->insert($requestData);
        $this->session->setFlash('success', 'Produto adicionado com sucesso');
        $this->response->redirect('/products');
    }

    public function edit($productId)
    {
        $productData = $this->product->find($productId);

        if (!$productData) {
            $this->response->abort(404);
        }

        $this->response->view('product/edit', [
            'product' => $productData
        ]);
    }

    public function update($productId)
    {
        $productData = $this->product->find($productId);

        if (!$productData) {
            $this->response->abort(404);
        }

        $requestData = [
            'name' => $this->request->post('name'),
            'barcode' => $this->request->post('barcode'),
            'is_active' => $this->request->post('is_active')
        ];

        $errors = $this->validator->fields($requestData, [
            'name' => 'required|string|max:60',
            'barcode' => "required|string|max:13|unique:product,barcode,{$productId}",
            'is_active' => 'required|in:0,1'
        ], [
            'name' => 'nome',
            'barcode' => 'código de barras',
            'is_active' => 'status'
        ]);

        if ($errors) {
            $this->session->setFlash('danger', $errors);
            $this->response->redirect('same_uri');
        }

        $this->product->update($requestData, $productId);
        $this->session->setFlash('success', 'Produto atualizado com sucesso');
        $this->response->redirect('/products');
    }

    public function delete($productId)
    {
        $productData = $this->product->find($productId);

        if (!$productData) {
            $this->response->abort(404);
        }

        if ($this->database->existsInTables($productId, 'product_id', ['award', 'redemption', 'supply'])) {
            $this->session->setFlash('danger', 'Não é possível excluir este produto');
            $this->response->redirect('/products/edit/' . $productId);
        }

        $this->product->delete($productId);
        $this->session->setFlash('success', 'Produto excluído com sucesso');
        $this->response->redirect('/products');
    }
}
