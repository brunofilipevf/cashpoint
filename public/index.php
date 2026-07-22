<?php

/*
|--------------------------------------------------------------------------
| Carrega os arquivos essenciais e captura exceções fatais
|--------------------------------------------------------------------------
*/

try {
    # Caminho absoluto da aplicação
    define('ABS_PATH', dirname(__DIR__));

    # Carrega autoloader, constantes e configurações da aplicação
    require ABS_PATH . '/autoload.php';
    require ABS_PATH . '/defines.php';
    require ABS_PATH . '/settings.php';

    # Cria o container de injeção de dependências
    $container = new Core\Container();

    # Carrega as rotas e executa o roteador
    require ABS_PATH . '/routes.php';

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
