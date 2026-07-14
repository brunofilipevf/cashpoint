<?php

namespace App\Controllers;

use App\Models\Product;
use Core\{Database, Request, Response, Session, Validator};

class ProductController
{
    public static function index()
    {
        Response::view('product/index', [
            'products' => Product::all()
        ]);
    }

    public static function add()
    {
        Response::view('product/add');
    }

    public static function insert()
    {
        $requestData = [
            'name' => Request::input('name'),
            'barcode' => Request::input('barcode')
        ];

        $errors = Validator::fields($requestData, [
            'name' => 'required|string|max:60',
            'barcode' => 'required|string|max:13|unique:product,barcode'
        ], [
            'name' => 'nome',
            'barcode' => 'código de barras'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::redirect('same_uri');
        }

        Product::insert($requestData);
        Session::setFlash('success', 'Produto adicionado com sucesso');
        Response::redirect('/products');
    }

    public static function edit($productId)
    {
        $productData = Product::find($productId);

        if (!$productData) {
            Response::abort(404);
        }

        Response::view('product/edit', [
            'product' => $productData
        ]);
    }

    public static function update($productId)
    {
        $productData = Product::find($productId);

        if (!$productData) {
            Response::abort(404);
        }

        $requestData = [
            'name' => Request::input('name'),
            'barcode' => Request::input('barcode'),
            'is_active' => Request::input('is_active')
        ];

        $errors = Validator::fields($requestData, [
            'name' => 'required|string|max:60',
            'barcode' => "required|string|max:13|unique:product,barcode,{$productId}",
            'is_active' => 'required|in:0,1'
        ], [
            'name' => 'nome',
            'barcode' => 'código de barras',
            'is_active' => 'status'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::redirect('same_uri');
        }

        Product::update($requestData, $productId);
        Session::setFlash('success', 'Produto atualizado com sucesso');
        Response::redirect('/products');
    }

    public static function delete($productId)
    {
        $productData = Product::find($productId);

        if (!$productData) {
            Response::abort(404);
        }

        if (Database::existsInTables($productId, 'product_id', ['award', 'redemption', 'supply'])) {
            Session::setFlash('danger', 'Não é possível excluir este produto');
            Response::redirect('/products/edit/' . $productId);
        }

        Product::delete($productId);
        Session::setFlash('success', 'Produto excluído com sucesso');
        Response::redirect('/products');
    }
}
