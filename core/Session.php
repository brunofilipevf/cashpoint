<?php

namespace Core;

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
        $session = &$_SESSION;

        foreach ($keys as $k) {
            $session = &$session[$k];
        }

        $session = $value;
    }

    public function get($key)
    {
        $keys = explode('.', $key);
        $session = $_SESSION;

        foreach ($keys as $k) {
            if (!isset($session[$k])) {
                return null;
            }
            $session = $session[$k];
        }

        return $session;
    }

    public function unset($key)
    {
        $keys = explode('.', $key);
        $last = array_pop($keys);
        $session = &$_SESSION;

        foreach ($keys as $k) {
            if (!isset($session[$k])) {
                return;
            }
            $session = &$session[$k];
        }

        unset($session[$last]);
    }

    public function destroy()
    {
        $_SESSION = [];
        $params = session_get_cookie_params();

        setcookie(session_name(), '', [
            'expires' => 0,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => 'Strict'
        ]);

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
        $type = $this->get('flash.type');
        $message = $this->get('flash.message');

        $this->unset('flash');

        if ($type === null || $message === null) {
            return [];
        }

        return [
            'type' => $type,
            'message' => $message
        ];
    }

    public function getCsrf()
    {
        $stored = $this->get('csrf_token');

        if ($stored === null) {
            $token = bin2hex(random_bytes(32));
            $this->set('csrf_token', $token);
            return $token;
        }

        return $stored;
    }

    public function validateCsrf($token)
    {
        $stored = $this->get('csrf_token');

        if ($stored === null || $token === null) {
            return false;
        }

        if (!hash_equals($stored, $token)) {
            return false;
        }

        $this->unset('csrf_token');
        return true;
    }
}
