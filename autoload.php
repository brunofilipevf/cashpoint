<?php

/*
|--------------------------------------------------------------------------
| Registra o autoloader de classes
|--------------------------------------------------------------------------
*/

spl_autoload_register(function ($class) {
    $file = ABS_PATH . '/' . str_replace('\\', '/', $class) . '.php';

    if (is_file($file)) {
        require $file;
    }
});
