<?php

class User
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

        $data['username'] = $this->request->post('username');
        $data['fullname'] = $this->request->post('fullname');
        $data['company_id'] = $this->request->post('company_id');
        $data['password'] = $this->request->post('password');
        $data['level'] = 1;
        $data['status'] = 1;
        $data['created_at'] = date('Y-m-d H:i:s');

        $this->validator->field($data['username'], 'nome de usuário')->rules('required|string|min:4|max:30|unique:users,username');
        $this->validator->field($data['fullname'], 'nome completo')->rules('required|string|min:4|max:100');
        $this->validator->field($data['company_id'], 'empresa')->rules('integer|exists:companies,id');
        $this->validator->field($data['password'], 'senha')->rules('required|string|min:6|max:30');

        if (!empty($this->validator->getErrors())) {
            $this->session->setFlash('danger', $this->validator->getErrors());
            return false;
        }

        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        if (!$this->insert($data)) {
            $this->session->setFlash('danger', 'Falha ao adicionar usuário.');
            return false;
        }

        $this->session->setFlash('success', 'Usuário adicionado com sucesso.');
        return true;
    }

    public function edit()
    {
        if (!$this->session->validateCSRF($this->request->post('csrf_token'))) {
            $this->session->setFlash('danger', 'Formulários expirado ou inválido.');
            return false;
        }

        $currentUserId = $this->session->get('auth.id');
        $id = $this->request->post('id');

        $this->validator->field($id, 'id do usuário')->rules('required|integer|exists:users,id');

        if (!empty($this->validator->getErrors())) {
            $this->session->setFlash('danger', $this->validator->getErrors());
            return false;
        }

        $currentUser = $this->getUserById($currentUserId);
        $targetUser = $this->getUserById($id);

        if (!$currentUser) {
            $this->session->setFlash('danger', 'Sessão inválida.');
            return false;
        }

        if (!$targetUser) {
            $this->session->setFlash('danger', 'Usuário não encontrado.');
            return false;
        }

        if ($targetUser['level'] >= $currentUser['level']) {
            $this->session->setFlash('danger', 'Você não tem permissão para alterar este usuário.');
            return false;
        }

        $data['fullname'] = $this->request->post('fullname');
        $data['company_id'] = $this->request->post('company_id');
        $data['level'] = $this->request->post('level');
        $data['status'] = $this->request->post('status');
        $data['password'] = $this->request->post('password');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->validator->field($data['fullname'], 'nome completo')->rules('required|string|min:4|max:100');
        $this->validator->field($data['company_id'], 'empresa')->rules('integer|exists:companies,id');
        $this->validator->field($data['level'], 'nível')->rules('required|integer|in:1,2,3,4,5');
        $this->validator->field($data['status'], 'status')->rules('required|integer|in:1,2');

        if (!empty($data['password'])) {
            $this->validator->field($data['password'], 'senha')->rules('string|min:6|max:30');
        }

        if (!empty($this->validator->getErrors())) {
            $this->session->setFlash('danger', $this->validator->getErrors());
            return false;
        }

        if ($id == $currentUserId && ($data['level'] != $currentUser['level'] || $data['status'] != $currentUser['status'])) {
            $this->session->setFlash('danger', 'Você não pode alterar seu próprio nível ou status.');
            return false;
        }

        if ($data['level'] >= $currentUser['level']) {
            $this->session->setFlash('danger', 'Você não pode atribuir um nível igual ou superior ao seu.');
            return false;
        }

        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']);
        }

        if (!$this->update($id, $data)) {
            $this->session->setFlash('danger', 'Falha ao atualizar usuário.');
            return false;
        }

        $this->session->setFlash('success', 'Usuário atualizado com sucesso.');
        return true;
    }

    private function getUserById($id)
    {
        try {
            $sql = "SELECT id, username, fullname, company_id, level, status, created_at, updated_at FROM `users` WHERE id = ? LIMIT 1";
            $stmt = Database::prepare($sql);
            $stmt->execute([$id]);

            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("[" . __CLASS__ . "@" . __METHOD__ . "]: " . $e->getMessage());
            return null;
        }
    }

    private function insert($data)
    {
        try {
            $fields = '`' . implode('`, `', array_keys($data)) . '`';
            $placeholders = ':' . implode(', :', array_keys($data));

            $sql = "INSERT INTO `users` ({$fields}) VALUES ({$placeholders})";
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

            $sql = "UPDATE `users` SET {$setClause} WHERE id = :id";
            $stmt = Database::prepare($sql);

            $data['id'] = $id;

            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("[" . __CLASS__ . "@" . __METHOD__ . "]: " . $e->getMessage());
            return false;
        }
    }
}