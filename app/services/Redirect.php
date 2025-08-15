<?php

namespace App\Services;

class Redirect
{
    public function to($path)
    {
        header('Location: /' . str_replace('.', '/', trim($path, '/')));
        exit();
    }

    public function back()
    {
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }

    public function withInput()
    {
        $post = $_POST;
        unset($post['password']);
        unset($post['csrf_token']);

        session()->set('old_input', $post);

        return $this;
    }

    public function withMessage($type, $message)
    {
        session()->setFlash($type, $message);
        return $this;
    }
}