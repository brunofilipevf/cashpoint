<?php

spl_autoload_register(function ($class) {
    $baseDirs = [
        'App\\' => ABSPATH . '/app/',
        'Services\\' => ABSPATH . '/services/',
    ];

    foreach ($baseDirs as $prefix => $base) {
        if (strpos($class, $prefix) !== 0) {
            continue;
        }

        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        $file = $base . $relative . '.php';

        if (is_file($file)) {
            require($file);
        }

        return;
    }
});
