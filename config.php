<?php

/*
|--------------------------------------------------------------------------
| Configuração do banco de dados
|--------------------------------------------------------------------------
*/

define('DB_HOST', 'localhost');
define('DB_NAME', 'cashpoint');
define('DB_CHAR', 'utf8mb4');
define('DB_USER', 'root');
define('DB_PASS', '');

/*
|--------------------------------------------------------------------------
| Configuração da aplicação
|--------------------------------------------------------------------------
*/

define('APP_URL', 'https://cashpoint.test');
define('APP_NAME', 'CashPoint');
define('APP_DESC', 'Sistema de CashPoint');
define('APP_DEBUG', true);
define('APP_TIMEZONE', 'America/Recife');

/*
|--------------------------------------------------------------------------
| Níveis de acesso
|--------------------------------------------------------------------------
*/

define('LEVELS', [
    1 => 'Frentista',
    2 => 'Supervisor',
    3 => 'Gerente',
    4 => 'Diretor',
    5 => 'Administrador',
]);

/*
|--------------------------------------------------------------------------
| Caminho absoluto
|--------------------------------------------------------------------------
*/

define('ABSPATH', __DIR__);