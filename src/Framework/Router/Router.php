<?php
declare(strict_types=1);

namespace Framework\Router;

use Framework\Request\Request;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function add(string $method, string $path, array $handler): void
    {
        $method = strtoupper($method);
        $this->routes[$method][] = ['path' => $path, 'handler' => $handler];
    }

    public function dispatch(Request $request): ?array
    {
        $method = $request->method;
        $path = rtrim($request->path, '/') ?: '/';

        foreach ($this->routes[$method] ?? [] as $route) {
            $pattern = preg_replace('#\{([^}]+)\}#', '(?P<$1>[^/]+)', $route['path']);
            $pattern = $route['path'] === '/' ? '/' : rtrim($pattern, '/');
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return [$route['handler'], $params];
            }
        }

        return null;
    }
}
