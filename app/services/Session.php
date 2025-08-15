<?php

namespace App\Services;

class Session
{
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function set($key, $value)
    {
        $keys = explode('.', $key);
        $current = &$_SESSION;

        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $current[$k] = $value;
            } else {
                if (!isset($current[$k]) || !is_array($current[$k])) {
                    $current[$k] = [];
                }
                $current = &$current[$k];
            }
        }
    }

    public function get($key, $default = null)
    {
        $keys = explode('.', $key);
        $current = $_SESSION;

        foreach ($keys as $k) {
            if (!isset($current[$k])) {
                return $default;
            }
            $current = $current[$k];
        }

        return $current;
    }

    public function pull($key, $default = null)
    {
        $value = $this->get($key, $default);
        $this->unset($key);
        return $value;
    }

    public function unset($key)
    {
        $keys = explode('.', $key);
        $current = &$_SESSION;

        foreach ($keys as $i => $k) {
            if (!isset($current[$k])) {
                return;
            }

            if ($i === count($keys) - 1) {
                unset($current[$k]);
                return;
            }

            $current = &$current[$k];
        }
    }

    public function destroy()
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_unset();
        session_destroy();
    }

    public function regenerate()
    {
        session_regenerate_id(true);
    }

    public function setFlash($type, $message)
    {
        if (is_array($message)) {
            $message = implode("\n", $message);
        }

        $this->set('flash.type', $type);
        $this->set('flash.message', $message);
    }

    public function getFlash()
    {
        $flash = $this->get('flash');
        if (empty($flash)) {
            return null;
        }

        $this->unset('flash');

        return $flash;
    }

    public function generateCSRF()
    {
        $sessionToken = $this->get('csrf_token');
        if (!empty($sessionToken)) {
            return $sessionToken;
        }

        $token = bin2hex(random_bytes(32));

        $this->set('csrf_token', $token);

        return $token;
    }

    public function validateCSRF($token)
    {
        $sessionToken = $this->get('csrf_token');

        $this->unset('csrf_token');

        if (empty($sessionToken) || empty($token)) {
            return false;
        }

        if (!is_string($sessionToken) || !is_string($token)) {
            return false;
        }

        if (strlen($sessionToken) !== 64 || strlen($token) !== 64) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }
}