<?php

namespace Services;

class Session
{
    private function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function set($key, $value)
    {
        $this->start();

        if (!is_array($key)) {
            $_SESSION[$key] = $value;
            return;
        }

        $session = &$_SESSION;

        foreach ($key as $segment) {
            if (!isset($session[$segment])) {
                $session[$segment] = [];
            }

            $session = &$session[$segment];
        }

        $session = $value;
    }

    public function get($key, $default = null)
    {
        $this->start();

        if (!is_array($key)) {
            return $_SESSION[$key] ?? $default;
        }

        $session = $_SESSION;

        foreach ($key as $segment) {
            if (!isset($session[$segment])) {
                return $default;
            }

            $session = $session[$segment];
        }

        return $session;
    }

    public function unset($key)
    {
        $this->start();

        if (!is_array($key)) {
            unset($_SESSION[$key]);
            return;
        }

        $session = &$_SESSION;
        $lastSegment = array_pop($key);

        foreach ($key as $segment) {
            if (!isset($session[$segment])) {
                return;
            }

            $session = &$session[$segment];
        }

        unset($session[$lastSegment]);
    }

    public function destroy()
    {
        $this->start();
        $_SESSION = [];
        session_destroy();
    }

    public function regenerate()
    {
        $this->start();
        session_regenerate_id(true);
    }

    public function setFlash($type, $message)
    {
        $this->start();

        if (is_array($message)) {
            $message = implode("\n", $message);
        }

        $_SESSION['flash']['type'] = $type;
        $_SESSION['flash']['message'] = $message;
    }

    public function getFlash()
    {
        $this->start();

        if (!isset($_SESSION['flash'])) {
            return null;
        }

        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return $flash;
    }

    public function generateCsrf()
    {
        $this->start();

        if (!isset($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf'];
    }

    public function validateCsrf($token)
    {
        $this->start();

        if (!isset($_SESSION['csrf'])) {
            return false;
        }

        if ($_SESSION['csrf'] !== $token) {
            return false;
        }

        unset($_SESSION['csrf']);
        return true;
    }
}
