<?php

namespace App\Controllers;

use App\Models\Product;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class ProductController
{
    public static function index()
    {
        $products = Product::all();
        Response::view('product/index', ['products' => $products]);
    }

    public static function add()
    {
        Response::view('product/add');
    }

    public static function insert()
    {
        $data = [
            'name' => Request::input('name'),
            'barcode' => Request::input('barcode'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $errors = Validator::fields($data, [
            'name' => 'required|string|min:2|max:60',
            'barcode' => 'required|string|max:13|unique:product,barcode'
        ], [
            'name' => 'nome',
            'barcode' => 'código de barras'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::previous();
        }

        $inserted = Product::insert($data);

        if (!$inserted) {
            Session::setFlash('danger', 'Erro ao adicionar registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro adicionado com sucesso');
        Response::redirect('/products');
    }

    public static function edit($id)
    {
        $targetProduct = Product::get($id);

        if (!$targetProduct) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        Response::view('product/edit', ['product' => $targetProduct]);
    }

    public static function update($id)
    {
        $targetProduct = Product::get($id);

        if (!$targetProduct) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        $data = [
            'name' => Request::input('name'),
            'barcode' => Request::input('barcode'),
            'is_active' => Request::input('is_active'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $errors = Validator::fields($data, [
            'name' => 'required|string|min:2|max:60',
            'barcode' => "required|string|max:13|unique:product,barcode,{$id}",
            'is_active' => 'required|in:0,1'
        ], [
            'name' => 'nome',
            'barcode' => 'código de barras',
            'is_active' => 'status'
        ]);

        if ($errors) {
            Session::setFlash('danger', $errors);
            Response::previous();
        }

        $updated = Product::update($data, $id);

        if (!$updated) {
            Session::setFlash('danger', 'Erro ao atualizar registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro atualizado com sucesso');
        Response::redirect('/products');
    }

    public static function delete($id)
    {
        $targetProduct = Product::get($id);

        if (!$targetProduct) {
            Session::setFlash('danger', 'Registro não encontrado');
            Response::previous();
        }

        $deleted = Product::delete($id);

        if (!$deleted) {
            Session::setFlash('danger', 'Erro ao excluir registro');
            Response::previous();
        }

        Session::setFlash('success', 'Registro excluído com sucesso');
        Response::redirect('/products');
    }
}
