<?php

define('APP_URL', rtrim('https://localhost', '/'));
define('APP_NAME', 'CashPoint');
define('APP_DESCRIPTION', 'Sistema de CashPoint');
define('APP_TIMEZONE', 'America/Recife');
define('APP_ENCODING', 'UTF-8');
define('APP_DEBUG', true);

define('DB_HOST', 'localhost');
define('DB_NAME', 'cashpoint');
define('DB_CHAR', 'utf8mb4');
define('DB_USER', 'root');
define('DB_PASS', '');

date_default_timezone_set(APP_TIMEZONE);
mb_internal_encoding(APP_ENCODING);
