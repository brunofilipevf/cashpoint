<?php

spl_autoload_register(function ($class) {
    $map = [
        'App\\' => ABSPATH . '/app/',
        'Services\\' => ABSPATH . '/services/',
    ];

    foreach ($map as $prefix => $base) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $length = strlen($prefix);
        $relative = substr($class, $length);
        $path = str_replace('\\', '/', $relative);
        $file = $base . $path . '.php';

        if (!is_file($file)) {
            continue;
        }

        require $file;
        return;
    }
});
