<?php

class Company
{
    private $request;
    private $session;
    private $validator;

    public function __construct()
    {
        $this->request = new Request();
        $this->session = new Session();
        $this->validator = new Validator();
    }

    public function add()
    {
        if (!$this->session->validateCSRF($this->request->post('csrf_token'))) {
            $this->session->setFlash('danger', 'Formulários expirado ou inválido.');
            return false;
        }

        $data['cpf'] = $this->request->post('cpf');
        $data['name'] = $this->request->post('name');
        $data['status'] = 1;
        $data['created_at'] = date('Y-m-d H:i:s');

        $this->validator->field($data['cpf'], 'CPF/CNPJ')->rules('required|numeric|length:11,14|unique:companies,cpf');
        $this->validator->field($data['name'], 'nome')->rules('required|string|min:4|max:100');

        if (!empty($this->validator->getErrors())) {
            $this->session->setFlash('danger', $this->validator->getErrors());
            return false;
        }

        if (!$this->insert($data)) {
            $this->session->setFlash('danger', 'Falha ao adicionar empresa.');
            return false;
        }

        $this->session->setFlash('success', 'Empresa adicionada com sucesso.');
        return true;
    }

    public function edit()
    {
        if (!$this->session->validateCSRF($this->request->post('csrf_token'))) {
            $this->session->setFlash('danger', 'Formulários expirado ou inválido.');
            return false;
        }

        $id = $this->request->post('id');

        $this->validator->field($id, 'id da empresa')->rules('required|integer|exists:companies,id');

        if (!empty($this->validator->getErrors())) {
            $this->session->setFlash('danger', $this->validator->getErrors());
            return false;
        }

        $data['name'] = $this->request->post('name');
        $data['status'] = $this->request->post('status');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->validator->field($data['name'], 'nome')->rules('required|string|min:4|max:100');
        $this->validator->field($data['status'], 'status')->rules('required|integer|in:1,2');

        if (!empty($this->validator->getErrors())) {
            $this->session->setFlash('danger', $this->validator->getErrors());
            return false;
        }

        if (!$this->update($id, $data)) {
            $this->session->setFlash('danger', 'Falha ao atualizar empresa.');
            return false;
        }

        $this->session->setFlash('success', 'Empresa atualizada com sucesso.');
        return true;
    }

    private function insert($data)
    {
        try {
            $fields = '`' . implode('`, `', array_keys($data)) . '`';
            $placeholders = ':' . implode(', :', array_keys($data));

            $sql = "INSERT INTO `companies` ({$fields}) VALUES ({$placeholders})";
            $stmt = Database::prepare($sql);

            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("[" . __CLASS__ . "@" . __METHOD__ . "]: " . $e->getMessage());
            return false;
        }
    }

    private function update($id, $data)
    {
        try {
            $setParts = array_map(
                fn($key) => "`{$key}` = :{$key}",
                array_keys($data)
            );
            $setClause = implode(', ', $setParts);

            $sql = "UPDATE `companies` SET {$setClause} WHERE id = :id";
            $stmt = Database::prepare($sql);

            $data['id'] = $id;

            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("[" . __CLASS__ . "@" . __METHOD__ . "]: " . $e->getMessage());
            return false;
        }
    }
}