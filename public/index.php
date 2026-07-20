<?php

/*
|--------------------------------------------------------------------------
| Carrega os arquivos essenciais e captura exceções fatais
|--------------------------------------------------------------------------
*/

try {

    # Carrega autoloader, constantes e configurações da aplicação
    require __DIR__ . '/../autoload.php';
    require __DIR__ . '/../defines.php';
    require __DIR__ . '/../settings.php';

    # Cria o container de injeção de dependências
    $container = new Core\Container();

    # Carrega as rotas e executa o roteador
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
