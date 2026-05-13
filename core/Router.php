<?php

namespace Core;

use RuntimeException;

class Router
{
    private static $routes = [];

    public static function get($path, $handler, $middlewares)
    {
        self::set('GET', $path, $handler, $middlewares);
    }

    public static function post($path, $handler, $middlewares)
    {
        self::set('POST', $path, $handler, $middlewares);
    }

    private static function set($method, $path, $handler, $middlewares)
    {
        # Escapa caracteres especiais da rota para uso seguro em regex
        # Substitui parâmetros numéricos da rota por regex de inteiros positivos, limitados a 10 dígitos (1 a 9999999999)
        # Define início e fim da expressão para match exato da URI
        $pattern = preg_quote($path, '/');
        $pattern = str_replace(['\{id\}', '\{page\}'], '([1-9][0-9]{0,9})', $pattern);
        $pattern = "/^{$pattern}$/";

        self::$routes[$method][] = [
            'pattern' => $pattern,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    public static function dispatch()
    {
        $method = Request::method();
        $uri = Request::uri();

        if (!isset(self::$routes[$method])) {
            return Response::send("Método HTTP não permitido", 405);
        }

        foreach (self::$routes[$method] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches);

                self::setPreviousUri($method, $uri);
                self::runMiddlewares($route['middlewares']);
                self::runController($route['handler'], $matches);

                return;
            }
        }

        return Response::send("Página não encontrada", 404);
    }

    private static function setPreviousUri($method, $uri)
    {
        $current = Session::get('current_uri');

        if ($method === 'GET') {
            # Em GET, a URI anterior vira previous, e a atual vira current
            if ($current !== null && $current !== $uri) {
                Session::set('previous_uri', $current);
            }

            Session::set('current_uri', $uri);
            return;
        }

        # Em POST, usa current_uri como previous para redirecionar de volta
        if ($current === null) {
            $current = '/';
        }

        Session::set('previous_uri', $current);
    }

    private static function runMiddlewares($middlewares)
    {
        foreach ($middlewares as $middleware) {
            if ($middleware === '') {
                continue;
            }

            $middlewareClass = 'App\\Middlewares\\' . $middleware;

            if (!class_exists($middlewareClass)) {
                throw new RuntimeException("Middleware '{$middleware}' não encontrado");
            }

            if (!method_exists($middlewareClass, 'handle')) {
                throw new RuntimeException("Método 'handle' não encontrado no middleware '{$middleware}'");
            }

            $middlewareClass::handle();
        }
    }

    private static function runController($handler, $matches)
    {
        [$controller, $action] = explode('@', $handler, 2);
        $controllerClass = 'App\\Controllers\\' . $controller;

        if (!class_exists($controllerClass)) {
            throw new RuntimeException("Controller '{$controller}' não encontrado");
        }

        if (!method_exists($controllerClass, $action)) {
            throw new RuntimeException("Action '{$action}' não encontrado no controller '{$controller}'");
        }

        $controllerClass::$action(...$matches);
    }
}
