<?php

ini_set('log_errors', '1');
ini_set('error_log', ABSPATH . '/storage/logs/php_errors.log');
ini_set('display_errors', '1');

error_reporting(E_ALL);

ini_set('session.save_handler', 'files');
ini_set('session.save_path', ABSPATH . '/storage/sessions');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.gc_maxlifetime', '7200');
ini_set('session.gc_probability', '1');
ini_set('session.gc_divisor', '100');
ini_set('session.cookie_lifetime', '0');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_samesite', 'Strict');

header_remove('X-Powered-By');
header_remove('X-XSS-Protection');

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
header("Content-Security-Policy: " . implode('; ', [
    "default-src 'self'",
    "base-uri 'self'",
    "form-action 'self'",
    "frame-ancestors 'none'",
    "object-src 'none'",
    "img-src 'self' data: https://*.cloudflare.com https://*.cloudflareinsights.com",
    "font-src 'self' https://*.cloudflare.com",
    "style-src 'self' https://*.cloudflare.com",
    "script-src 'self' https://*.cloudflare.com https://*.cloudflareinsights.com",
    "connect-src 'self' https://*.cloudflare.com https://*.cloudflareinsights.com",
    "media-src 'self'",
    "worker-src 'self' https://*.cloudflare.com",
    "manifest-src 'self'",
    "upgrade-insecure-requests",
]));

