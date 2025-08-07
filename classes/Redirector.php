<?php

class Redirector
{
    public function to($path)
    {
        $path = filter_var($path, FILTER_SANITIZE_URL);

        if (empty($path)) {
            $path = 'index.php';
        }

        header('Location: /' . ltrim($path, '/'), true, 302);
        exit();
    }

    public function back()
    {
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];

            if (filter_var($referer, FILTER_VALIDATE_URL) && strpos($referer, APP_URL) === 0) {
                header('Location: ' . $referer, true, 302);
                exit();
            }
        }

        $this->to('index.php');
    }
}