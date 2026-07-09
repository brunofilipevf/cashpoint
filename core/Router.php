<?php

namespace Core;

class Router
{
    private static $routes = [];

    public static function get($path, $handler, $middlewares)
    {
        self::add('GET', $path, $handler, $middlewares);
    }

    public static function post($path, $handler, $middlewares)
    {
        self::add('POST', $path, $handler, $middlewares);
    }

    private static function add($method, $path, $handler, $middlewares)
    {
        $pattern = preg_quote($path, '/');
        $pattern = str_replace('\\{id\\}', '([1-9][0-9]{0,9})', $pattern);
        $pattern = str_replace('\\{page\\}', '(?:\\/page\\/([1-9][0-9]{0,9}))?', $pattern);
        $pattern = "/^{$pattern}$/";

        self::$routes[$method][] = [
            'pattern' => $pattern,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    public static function dispatch()
    {
        Throttle::handle();

        $method = Request::method();
        $uri = Request::uri();

        if (!isset(self::$routes[$method])) {
            Response::abort(405);
        }

        foreach (self::$routes[$method] as $route) {
            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }

            self::executeMiddleware($route['middlewares']);
            self::executeController($route['handler'], $matches);

            return;
        }

        Response::abort(404);
    }

    private static function executeMiddleware($middlewares)
    {
        foreach ($middlewares as $middleware) {
            $parts = explode(':', $middleware, 2);
            $class = $parts[0];
            $fullClass = "App\\Middlewares\\{$class}";

            if (!class_exists($fullClass)) {
                throw new \RuntimeException("[Router] Middleware '{$fullClass}' não encontrado");
            }

            if (!method_exists($fullClass, 'handle')) {
                throw new \RuntimeException("[Router] Método 'handle' não existe em '{$fullClass}'");
            }

            if (isset($parts[1])) {
                $fullClass::handle((int) $parts[1]);
            } else {
                $fullClass::handle();
            }
        }
    }

    private static function executeController($handler, $matches)
    {
        $parts = explode('@', $handler, 2);
        $class = $parts[0];
        $method = $parts[1];
        $fullClass = "App\\Controllers\\{$class}";

        if (!class_exists($fullClass)) {
            throw new \RuntimeException("[Router] Controller '{$fullClass}' não encontrado");
        }

        if (!method_exists($fullClass, $method)) {
            throw new \RuntimeException("[Router] Método '{$method}' não existe em '{$fullClass}'");
        }

        array_shift($matches);
        $params = array_map('intval', $matches);

        $fullClass::$method(...$params);
    }
}
