<?php

namespace Services;

class Router
{
    private $container;
    private $routes = [];

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function get($path, $handler, $middlewares = [])
    {
        $this->routes['GET'][] = [
            'pattern' => '#^' . preg_replace('#\{id\}#', '([1-9]+)', $path) . '$#',
            'handler' => $handler,
            'middlewares' => (array) $middlewares
        ];
    }

    public function post($path, $handler, $middlewares = [])
    {
        $this->routes['POST'][] = [
            'pattern' => '#^' . preg_replace('#\{id\}#', '([1-9]+)', $path) . '$#',
            'handler' => $handler,
            'middlewares' => (array) $middlewares
        ];
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (!isset($this->routes[$method])) {
            http_response_code(405);
            echo 'Método não permitido';
            return;
        }

        foreach ($this->routes[$method] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches);

                foreach ($route['middlewares'] as $middleware) {
                    $middlewareClass = 'App\\Middlewares\\' . $middleware;
                    $this->container->get($middlewareClass)->handle();
                }

                [$controller, $action] = explode('@', $route['handler'], 2);
                $controllerClass = 'App\\Controllers\\' . $controller;

                $instance = $this->container->get($controllerClass);
                call_user_func_array([$instance, $action], $matches);
                return;
            }
        }

        http_response_code(404);
        echo 'Página não encontrada';
    }
}
