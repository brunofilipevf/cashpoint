<?php

require_once(__DIR__ . '/../bootstrap.php');

$route = new App\Services\Router();

$route->get('/', function() {
    return 'Rota de teste';
});

$route->dispatch();