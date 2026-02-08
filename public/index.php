<?php

define('ABSPATH', dirname(__DIR__));

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    require ABSPATH . '/boot/app.php';

    $router = Services\Router::getInstance();

    require ABSPATH . '/routes/web.php';

    $router->dispatch();
} catch (Throwable $e) {
    error_log($e->__toString());

    $content = 'Erro interno no servidor';

    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8', true);
    header('Content-Length: ' . strlen($content), true);

    echo $content;
}
