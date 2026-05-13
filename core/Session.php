<?php

namespace Core;

class Session
{
    public static function set($key, $value)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $keys = explode('.', $key);
        $session = &$_SESSION;

        foreach ($keys as $k) {
            $session = &$session[$k];
        }

        $session = $value;
    }

    public static function get($key)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

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
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

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
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

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

    public static function regenerate()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_regenerate_id(true);
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
        $type = self::get('flash.type');
        $message = self::get('flash.message');

        self::unset('flash');

        if ($type === null || $message === null) {
            return [];
        }

        return [
            'type' => $type,
            'message' => $message
        ];
    }

    public static function getCsrf()
    {
        $stored = self::get('csrf_token');

        if ($stored === null) {
            $token = bin2hex(random_bytes(32));
            self::set('csrf_token', $token);
            return $token;
        }

        return $stored;
    }

    public static function validateCsrf($token)
    {
        $stored = self::get('csrf_token');

        if ($stored === null || $token === null) {
            return false;
        }

        if (!hash_equals($stored, $token)) {
            return false;
        }

        self::unset('csrf_token');
        return true;
    }
}
