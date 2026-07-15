<?php

namespace Core;

class Session
{
    public function set($key, $value)
    {
        $this->startSession();

        $keys = explode('.', $key);
        $session = &$_SESSION;

        foreach ($keys as $k) {
            $session = &$session[$k];
        }

        $session = $value;
    }

    public function get($key)
    {
        $this->startSession();

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
        $this->startSession();

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
        $this->startSession();

        $_SESSION = [];

        $param = session_get_cookie_params();

        setcookie(session_name(), '', [
            'expires' => 0,
            'path' => $param['path'],
            'domain' => $param['domain'],
            'secure' => $param['secure'],
            'httponly' => $param['httponly'],
            'samesite' => 'Strict'
        ]);

        session_destroy();
    }

    public function regenerate()
    {
        $this->startSession();

        session_regenerate_id(true);
    }

    public function getCsrf()
    {
        $stored = $this->get('csrf_token');

        if ($stored !== null) {
            return $stored;
        }

        $token = bin2hex(random_bytes(32));
        $this->set('csrf_token', $token);

        return $token;
    }

    public function verifyCsrf($token)
    {
        $stored = $this->get('csrf_token');

        if ($stored === null || $token === null) {
            return false;
        }

        if (!hash_equals($stored, $token)) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $this->set('csrf_token', $token);

        return true;
    }

    public function setFlash($type, $content)
    {
        if (is_array($content)) {
            $content = implode("\n", $content);
        }

        $this->set('flash.type', $type);
        $this->set('flash.content', $content);
    }

    public function getFlash()
    {
        $flash = [
            'type' => $this->get('flash.type'),
            'content' => $this->get('flash.content')
        ];

        $this->unset('flash');

        if ($flash['type'] === null || $flash['content'] === null) {
            return [];
        }

        return $flash;
    }

    private function startSession()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_name('PHPSESSID');
            session_save_path(__DIR__ . '/../storage');
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);

            session_start();
        }

        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
            session_regenerate_id(true);
        } elseif (time() - $_SESSION['last_regeneration'] >= 300) {
            $_SESSION['last_regeneration'] = time();
            session_regenerate_id(true);
        }
    }
}
