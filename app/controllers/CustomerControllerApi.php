<?php

namespace App\Controllers;

class CustomerControllerApi
{
    public function __construct(
        private \App\Models\Customer $customer,
        private \Core\Request $request,
        private \Core\Response $response,
        private \Core\Validator $validator
    ) {}

    public function add()
    {
        $fillables = [
            'cpf' => 'CPF/CNPJ',
            'fullname' => 'Nome completo',
            'email' => 'E-mail',
            'phone' => 'Telefone'
        ];

        $this->response->json($fillables);
    }

    public function insert()
    {
        try {

            $requestData = [
                'cpf' => $this->request->json()['cpf'],
                'fullname' => $this->request->json()['fullname'],
                'email' => $this->request->json()['email'],
                'phone' => $this->request->json()['phone']
            ];

            $errors = $this->validator->fields($requestData, [
                'cpf' => 'required|document|unique:customer,cpf',
                'fullname' => 'string|max:60',
                'email' => 'email|unique:customer,email',
                'phone' => 'phone|unique:customer,phone'
            ], [
                'cpf' => 'CPF/CNPJ',
                'fullname' => 'nome completo',
                'email' => 'e-mail',
                'phone' => 'celular'
            ]);

            if ($errors) {
                $this->response->json(['error', $errors[0]]);
            }

            $this->customer->insert($requestData);
            $this->response->json(['success', 'Cliente adicionado com sucesso']);

        } catch (\Exception) {
            $this->response->json(['error', 'Erro ao adicionar cliente']);
        }
    }
}
