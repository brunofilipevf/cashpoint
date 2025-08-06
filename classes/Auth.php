<?php

class Auth
{
    private $request;
    private $session;
    private $validator;
    private $maxDowntime = 900;

    public function __construct()
    {
        $this->request = new Request();
        $this->session = new Session();
        $this->validator = new Validator();
    }

    public function login()
    {
        if (!$this->session->validateCSRF($this->request->post('csrf_token'))) {
            $this->session->setFlash('danger', 'Formulários expirado ou inválido.');
            return false;
        }

        $data['username'] = $this->request->post('username');
        $data['password'] = $this->request->post('password');

        $this->validator->field($data['username'], 'nome de usuário')->rules('required|string');
        $this->validator->field($data['password'], 'senha')->rules('required|string');

        if (!empty($this->validator->getErrors())) {
            $this->session->setFlash('danger', $this->validator->getErrors());
            return false;
        }

        $user = $this->getUserByUsername($data['username']);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            $this->session->setFlash('danger', 'Credenciais inválidas ou usuário inativo.');
            return false;
        }

        $this->session->regenerate();
        $this->session->set('auth.id', $user['id']);
        $this->session->set('auth.time', time());

        return true;
    }

    public function logout()
    {
        $this->session->destroy();
    }

    public function isLogged()
    {
        $userId = $this->session->get('auth.id');

        if (empty($userId)) {
            return false;
        }

        $lastActivity = $this->session->get('auth.time');

        if (empty($lastActivity) || (time() - $lastActivity > $this->maxDowntime)) {
            $this->logout();
            return false;
        }

        if (!$this->getUserById($userId)) {
            $this->logout();
            return false;
        }

        $this->session->set('auth.time', time());
        return true;
    }

    private function getUserByUsername($username)
    {
        try {
            $sql = "SELECT id, password FROM `users` WHERE username = ? AND status = 1 LIMIT 1";
            $stmt = Database::prepare($sql);
            $stmt->execute([$username]);
            return $stmt->fetch() ?? null;
        } catch (PDOException $e) {
            error_log("[" . __CLASS__ . "@" . __METHOD__ . "]: " . $e->getMessage());
            return null;
        }
    }

    private function getUserById($id)
    {
        try {
            $sql = "SELECT id FROM `users` WHERE id = ? AND status = 1 LIMIT 1";
            $stmt = Database::prepare($sql);
            $stmt->execute([$id]);
            return (bool) $stmt->fetch();
        } catch (PDOException $e) {
            error_log("[" . __CLASS__ . "@" . __METHOD__ . "]: " . $e->getMessage());
            return false;
        }
    }
}