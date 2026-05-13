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
        return Response::view('product/index', ['products' => $products]);
    }

    public static function add()
    {
        return Response::view('product/add');
    }

    public static function insert()
    {
        $data = [
            'name' => Request::input('name'),
            'barcode' => Request::input('barcode'),
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

        $errors = Validator::fields($data, $rules, $labels);

        if ($errors) {
            Session::setFlash('danger', $errors);
            return Response::previous();
        }

        $inserted = Product::insert($data);

        if (!$inserted) {
            Session::setFlash('danger', 'Erro ao adicionar registro');
            return Response::previous();
        }

        Session::setFlash('success', 'Registro adicionado com sucesso');
        return Response::redirect('/products');
    }

    public static function edit($id)
    {
        $targetProduct = Product::get($id);

        if (!$targetProduct) {
            Session::setFlash('danger', 'Registro não encontrado');
            return Response::previous();
        }

        return Response::view('product/edit', ['product' => $targetProduct]);
    }

    public static function update($id)
    {
        $targetProduct = Product::get($id);

        if (!$targetProduct) {
            Session::setFlash('danger', 'Registro não encontrado');
            return Response::previous();
        }

        $data = [
            'name' => Request::input('name'),
            'barcode' => Request::input('barcode'),
            'is_active' => Request::input('is_active'),
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

        $errors = Validator::fields($data, $rules, $labels);

        if ($errors) {
            Session::setFlash('danger', $errors);
            return Response::previous();
        }

        $updated = Product::update($data, $id);

        if (!$updated) {
            Session::setFlash('danger', 'Erro ao atualizar registro');
            return Response::previous();
        }

        Session::setFlash('success', 'Registro atualizado com sucesso');
        return Response::redirect('/products');
    }

    public static function delete($id)
    {
        $targetProduct = Product::get($id);

        if (!$targetProduct) {
            Session::setFlash('danger', 'Registro não encontrado');
            return Response::previous();
        }

        $deleted = Product::delete($id);

        if (!$deleted) {
            Session::setFlash('danger', 'Erro ao excluir registro');
            return Response::previous();
        }

        Session::setFlash('success', 'Registro excluído com sucesso');
        return Response::redirect('/products');
    }
}
