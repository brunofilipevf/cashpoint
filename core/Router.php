<?php

namespace Core;

use RuntimeException;

class Router
{
    private $routes = [];

    public function __construct(
        private Container $container
    ) { }

    public function get($path, $handler, $middlewares)
    {
        $this->set('GET', $path, $handler, $middlewares);
    }

    public function post($path, $handler, $middlewares)
    {
        $this->set('POST', $path, $handler, $middlewares);
    }

    private function set($method, $path, $handler, $middlewares)
    {
        $pattern = preg_quote($path, '/');
        $pattern = str_replace(['\{id\}', '\{page\}'], '([1-9][0-9]{0,9})', $pattern);
        $pattern = "/^{$pattern}$/";

        $this->routes[$method][] = [
            'pattern' => $pattern,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    public function dispatch()
    {
        $method = $this->request()->method();
        $uri = $this->request()->uri();

        if (!isset($this->routes[$method])) {
            return $this->response()->send("Método HTTP não permitido", 405);
        }

        foreach ($this->routes[$method] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches);

                $this->setPreviousUri($method, $uri);
                $this->runMiddlewares($route['middlewares']);
                $this->runController($route['handler'], $matches);

                return;
            }
        }

        return $this->response()->send("Página não encontrada", 404);
    }

    private function setPreviousUri($method, $uri)
    {
        $current = $this->session()->get('current_uri');

        if ($method === 'GET') {
            # Em GET, a URI anterior vira previous, e a atual vira current
            if ($current !== null && $current !== $uri) {
                $this->session()->set('previous_uri', $current);
            }

            $this->session()->set('current_uri', $uri);
            return;
        }

        # Em POST, usa current_uri como previous para redirecionar de volta
        if ($current === null) {
            $current = '/';
        }

        $this->session()->set('previous_uri', $current);
    }

    private function runMiddlewares($middlewares)
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

            $middlewareInstance = $this->container->get($middlewareClass);
            $middlewareInstance->handle();
        }
    }

    private function runController($handler, $matches)
    {
        [$controller, $action] = explode('@', $handler, 2);
        $controllerClass = 'App\\Controllers\\' . $controller;

        if (!class_exists($controllerClass)) {
            throw new RuntimeException("Controller '{$controller}' não encontrado");
        }

        if (!method_exists($controllerClass, $action)) {
            throw new RuntimeException("Action '{$action}' não encontrado no controller '{$controller}'");
        }

        $controllerInstance = $this->container->get($controllerClass);
        $controllerInstance->$action(...$matches);
    }

    private function request()
    {
        return $this->container->get(Request::class);
    }

    private function response()
    {
        return $this->container->get(Response::class);
    }

    private function session()
    {
        return $this->container->get(Session::class);
    }
}
