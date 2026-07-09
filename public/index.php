<?php

/*
|--------------------------------------------------------------------------
| Carrega os arquivos essenciais e captura exceções fatais
|--------------------------------------------------------------------------
*/

try {
    require __DIR__ . '/../autoload.php';
    require __DIR__ . '/../defines.php';
    require __DIR__ . '/../config.php';
    require __DIR__ . '/../routes.php';
} catch (RuntimeException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Erro interno do servidor');
} catch (Throwable $e) {
    error_log((string) $e);
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Erro interno do servidor');
}
