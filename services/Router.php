<?php

namespace Services;

use RuntimeException;

class Router
{
    private static $instance;
    private $routes = [];

    public static function getInstance()
    {
        return self::$instance ?? self::$instance = new self();
    }

    public function get($path, $handler, $middlewares = [])
    {
        $this->add('GET', $path, $handler, $middlewares);
    }

    public function post($path, $handler, $middlewares = [])
    {
        $this->add('POST', $path, $handler, $middlewares);
    }

    private function add($method, $path, $handler, $middlewares)
    {
        $pattern = '#^' . str_replace('{id}', '([1-9][0-9]*)', $path) . '$#';

        $this->routes[$method][] = [
            'pattern' => $pattern,
            'handler' => $handler,
            'middlewares' => (array) $middlewares,
        ];
    }

    private function runMiddlewares($middlewares)
    {
        foreach ($middlewares as $middleware) {
            if ($middleware === '') {
                continue;
            }

            $class = 'App\\Middlewares\\' . $middleware;

            if (!class_exists($class)) {
                throw new RuntimeException("Middleware {$class} não encontrado");
            }

            $instance = $class::getInstance();

            if (!method_exists($instance, 'handle')) {
                throw new RuntimeException("Middleware {$class} sem método handle");
            }

            $instance->handle();
        }
    }

    private function runController($handler, $params)
    {
        [$controller, $method] = explode('@', $handler, 2);

        $class = 'App\\Controllers\\' . $controller;

        if (!class_exists($class)) {
            throw new RuntimeException("Controller {$class} não encontrado");
        }

        $instance = $class::getInstance();

        if (!method_exists($instance, $method)) {
            throw new RuntimeException("Método {$method} não encontrado em {$class}");
        }

        $instance->$method(...$params);
    }

    public function dispatch()
    {
        $request = Request::getInstance();
        $response = Response::getInstance();

        $method = $request->getMethod();
        $uri = $request->getUri();

        if (!isset($this->routes[$method])) {
            return $response->send('Método HTTP não suportado', 405);
        }

        foreach ($this->routes[$method] as $route) {
            if (!preg_match($route['pattern'], $uri, $params)) {
                continue;
            }

            array_shift($params);
            $this->runMiddlewares($route['middlewares']);
            $this->runController($route['handler'], $params);
            return;
        }

        return $response->send('Página não encontrada', 404);
    }
}
