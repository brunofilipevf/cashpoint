<?php

# Configurações da aplicação
define('APP_NAME', 'CashPoint');
define('APP_AUTHOR', 'Bruno Freitas');
define('APP_DESCRIPTION', 'Sistema de CashPoint');

# Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'cashpoint');
define('DB_CHAR', 'utf8mb4');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3306);

# Configurações de fuso horário
date_default_timezone_set('America/Recife');

# Configurações de erro e log
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/storage/php_errors.log');
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

# Configurações de sessão
ini_set('session.save_path', dirname(__DIR__) . '/storage');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.cookie_lifetime', '0');
ini_set('session.sid_length', '64');
ini_set('session.sid_bits_per_character', '6');
ini_set('session.gc_maxlifetime', '1800');
ini_set('session.gc_probability', '1');
ini_set('session.gc_divisor', '100');

# Configurações de limites
ini_set('memory_limit', '128M');
ini_set('upload_max_filesize', '2M');
ini_set('post_max_size', '2M');
ini_set('max_execution_time', '30');
ini_set('max_input_time', '60');
ini_set('max_input_vars', '1000');

# Configurações de segurança adicional
ini_set('expose_php', '0');
ini_set('enable_dl', '0');
ini_set('allow_url_fopen', '0');
ini_set('allow_url_include', '0');
