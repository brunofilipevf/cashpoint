<?php

/*
|--------------------------------------------------------------------------
| Definições gerais da aplicação
|--------------------------------------------------------------------------
*/

define('APP_NAME', Core\Environment::get('APP_NAME'));
define('APP_AUTHOR', Core\Environment::get('APP_AUTHOR'));
define('APP_DESCRIPTION', Core\Environment::get('APP_DESCRIPTION'));
define('APP_TIMEZONE', Core\Environment::get('APP_TIMEZONE'));

/*
|--------------------------------------------------------------------------
| Definições do banco de dados
|--------------------------------------------------------------------------
*/

define('DB_HOST', Core\Environment::get('DB_HOST'));
define('DB_NAME', Core\Environment::get('DB_NAME'));
define('DB_CHAR', Core\Environment::get('DB_CHAR'));
define('DB_USER', Core\Environment::get('DB_USER'));
define('DB_PASS', Core\Environment::get('DB_PASS'));
define('DB_PORT', Core\Environment::get('DB_PORT'));

/*
|--------------------------------------------------------------------------
| Definições de limites
|--------------------------------------------------------------------------
*/

define('MAX_VALUE_LIMIT_MULTIPLIER_FACTOR', Core\Environment::get('MAX_VALUE_LIMIT_MULTIPLIER_FACTOR'));
define('MAX_VALUE_LIMIT_MANUAL_POINTS', Core\Environment::get('MAX_VALUE_LIMIT_MANUAL_POINTS'));
define('MAX_DAILY_LIMIT_POINTS_PER_CUSTOMER', Core\Environment::get('MAX_DAILY_LIMIT_POINTS_PER_CUSTOMER'));

/*
|--------------------------------------------------------------------------
| Definições de e-mail
|--------------------------------------------------------------------------
*/

define('MAIL_HOST', Core\Environment::get('MAIL_HOST'));
define('MAIL_PORT', Core\Environment::get('MAIL_PORT'));
define('MAIL_USER', Core\Environment::get('MAIL_USER'));
define('MAIL_PASS', Core\Environment::get('MAIL_PASS'));

/*
|--------------------------------------------------------------------------
| Templates de e-mail para notificações
|--------------------------------------------------------------------------
*/

define('EMAIL_POINTS_CREDITED', Core\Environment::get('EMAIL_POINTS_CREDITED'));
define('EMAIL_REWARD_REDEEMED', Core\Environment::get('EMAIL_REWARD_REDEEMED'));
