<?php

/*
|--------------------------------------------------------------------------
| Define o fuso horário padrão
|--------------------------------------------------------------------------
*/

date_default_timezone_set(APP_TIMEZONE);

/*
|--------------------------------------------------------------------------
| Configurações de exibição e log de erros
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('error_log', __DIR__ . '/storage/php_errors.log');

/*
|--------------------------------------------------------------------------
| Converte erros do PHP em exceções para tratamento uniforme
|--------------------------------------------------------------------------
*/

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, $severity, $severity, $file, $line);
});
