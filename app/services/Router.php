<?php

namespace App\Services;

use Closure;

class Router
{
    private $routes = [];

    public function get($route, $controller, $middlewares = [])
    {
        $this->addRoute('GET', $route, $controller, $middlewares);
    }

    public function post($route, $controller, $middlewares = [])
    {
        $this->addRoute('POST', $route, $controller, $middlewares);
    }

    public function addRoute($method, $route, $controller, $middlewares = [])
    {
        $pattern = preg_replace('/\{([a-z0-9_]+)\}/', '(?P<\1>[^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';

        preg_match_all('/\{([a-z0-9_]+)\}/', $route, $paramNames);

        $this->routes[] = [
            'method'      => $method,
            'route'       => $route,
            'pattern'     => $pattern,
            'params'      => $paramNames[1],
            'controller'  => $controller,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                $params = [];
                foreach ($route['params'] as $paramName) {
                    $params[$paramName] = $this->sanitizeParam($matches[$paramName]);
                }

                $this->handleMiddlewares($route['middlewares']);
                $this->handleController($route['controller'], $params);

                return;
            }
        }

        http_response_code(404);
        echo 'Ops! Algo deu errado. Rota não encontrada.';
    }

    private function handleMiddlewares($middlewares)
    {
        foreach ($middlewares as $middleware) {
            $parts = array_map('trim', explode(':', $middleware));
            $class = 'App\\Middlewares\\' . $parts[0];

            $params = [];
            for ($i = 1; $i < count($parts); $i++) {
                $params[] = $this->sanitizeParam($parts[$i]);
            }

            $instance = new $class();
            $instance->handle(...$params);
        }
    }

    private function handleController($controller, $params = [])
    {
        if ($controller instanceof Closure) {
            echo $controller(...array_values($params));
        } else {
            $parts = explode('@', $controller);
            $class = 'App\\Controllers\\' . $parts[0];
            $methodName = $parts[1] ?? 'index';

            $instance = new $class();

            echo $instance->$methodName(...array_values($params));
        }
    }

    private function sanitizeParam($param)
    {
        $param = trim($param);
        $param = filter_var($param, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

        return $param;
    }
}