<?php

namespace Core;

class Session
{
    private static function init()
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

    public static function set($key, $value)
    {
        self::init();

        $keys = explode('.', $key);
        $session = &$_SESSION;

        foreach ($keys as $k) {
            $session = &$session[$k];
        }

        $session = $value;
    }

    public static function get($key)
    {
        self::init();

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

    public static function unset($key)
    {
        self::init();

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

    public static function destroy()
    {
        self::init();

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

    public static function regenerate()
    {
        self::init();

        session_regenerate_id(true);
    }

    public static function getCsrf()
    {
        $sessionToken = self::get('csrf_token');

        if ($sessionToken !== null) {
            return $sessionToken;
        }

        $token = bin2hex(random_bytes(32));
        self::set('csrf_token', $token);

        return $token;
    }

    public static function verifyCsrf($token)
    {
        $sessionToken = self::get('csrf_token');

        if ($sessionToken === null || $token === null) {
            return false;
        }

        if (!hash_equals($sessionToken, $token)) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        self::set('csrf_token', $token);

        return true;
    }

    public static function setFlash($type, $message)
    {
        if (is_array($message)) {
            $message = implode("\n", $message);
        }

        self::set('flash.type', $type);
        self::set('flash.message', $message);
    }

    public static function getFlash()
    {
        $flash = [
            'type' => self::get('flash.type'),
            'message' => self::get('flash.message')
        ];

        self::unset('flash');

        if ($flash['type'] === null || $flash['message'] === null) {
            return [];
        }

        return $flash;
    }
}
