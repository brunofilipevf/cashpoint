<?php

class Redirector
{
    public function to($path)
    {
        header('Location: /' . ltrim($path, '/'));
        exit();
    }

    public function back()
    {
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            $this->to($_SERVER['HTTP_REFERER']);
        } else {
            $this->to('index.php');
        }
    }
}