<?php

use Core\Container;
use Core\Router;

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, $severity, $severity, $file, $line);
});

try {
    require dirname(__DIR__) . '/boot/autoload.php';
    require dirname(__DIR__) . '/boot/config.php';
    $container = new Container();
    $router = $container->get(Router::class);
    require dirname(__DIR__) . '/boot/routes.php';
    $router->dispatch();
} catch (RuntimeException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Erro Interno da Aplicação");
} catch (Throwable $e) {
    error_log((string) $e);
    http_response_code(500);
    exit("Erro Interno do Servidor");
}
