<?php

spl_autoload_register(function ($class) {
    $prefixes = [
        'App\\' => dirname(__DIR__) . '/app/',
        'Core\\' => dirname(__DIR__) . '/core/'
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = mb_strlen($prefix, 'UTF-8');

        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        $name = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $name) . '.php';

        if (file_exists($file)) {
            require $file;
        }
    }
});
