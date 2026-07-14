<?php

namespace Core;

class Router
{
    private $routes = [];

    public function __construct(
        private Container $container,
        private Request $request,
        private Response $response,
        private Throttle $throttle
    ) {}

    public function get($path, $handler, $middlewares)
    {
        $this->add('GET', $path, $handler, $middlewares);
    }

    public function post($path, $handler, $middlewares)
    {
        $this->add('POST', $path, $handler, $middlewares);
    }

    private function add($method, $path, $handler, $middlewares)
    {
        $pattern = preg_quote($path, '/');
        $pattern = str_replace('\\{id\\}', '([1-9][0-9]{0,9})', $pattern);
        $pattern = str_replace('\\{page\\}', '(?:\\/page\\/([1-9][0-9]{0,9}))?', $pattern);
        $pattern = "/^{$pattern}$/";

        $this->routes[$method][] = [
            'pattern' => $pattern,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    public function dispatch()
    {
        $this->throttle->handle();

        $method = $this->request->method();
        $uri = $this->request->uri();

        if (!isset($this->routes[$method])) {
            $this->response->abort(405);
        }

        foreach ($this->routes[$method] as $route) {
            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }

            $this->executeMiddleware($route['middlewares']);
            $this->executeController($route['handler'], $matches);

            return;
        }

        $this->response->abort(404);
    }

    private function executeMiddleware($middlewares)
    {
        foreach ($middlewares as $middleware) {
            $parts = explode(':', $middleware, 2);
            $class = $parts[0];
            $instance = $this->container->get("App\\Middlewares\\{$class}");

            if (isset($parts[1])) {
                $instance->handle((int) $parts[1]);
            } else {
                $instance->handle();
            }
        }
    }

    private function executeController($handler, $matches)
    {
        $parts = explode('@', $handler, 2);
        $class = $parts[0];
        $method = $parts[1];
        $instance = $this->container->get("App\\Controllers\\{$class}");

        array_shift($matches);
        $params = array_map('intval', $matches);

        $instance->$method(...$params);
    }
}
