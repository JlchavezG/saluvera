<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class Router
{
    private array $routes = [];
    private array $groupStack = [];
    private ?Request $request = null;
    private ?Response $response = null;

    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
    }

    public function get(string $path, mixed $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, mixed $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, mixed $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function patch(string $path, mixed $handler): self
    {
        return $this->addRoute('PATCH', $path, $handler);
    }

    public function delete(string $path, mixed $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    public function any(string $path, mixed $handler): self
    {
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            $this->addRoute($method, $path, $handler);
        }
        return $this;
    }

    private function addRoute(string $method, string $path, mixed $handler): self
    {
        $path = $this->buildPath($path);

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middlewares' => $this->getCurrentMiddlewares(),
            'name' => null,
        ];

        return $this;
    }

    private function buildPath(string $path): string
    {
        $prefix = '';
        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= $group['prefix'];
            }
        }

        $fullPath = $prefix . '/' . trim($path, '/');
        $fullPath = '/' . trim($fullPath, '/');
        return $fullPath === '//' ? '/' : $fullPath;
    }

    private function getCurrentMiddlewares(): array
    {
        $middlewares = [];
        foreach ($this->groupStack as $group) {
            if (isset($group['middlewares']) && is_array($group['middlewares'])) {
                $middlewares = array_merge($middlewares, $group['middlewares']);
            }
        }
        return $middlewares;
    }

    public function name(string $name): self
    {
        $lastIndex = count($this->routes) - 1;
        if ($lastIndex >= 0) {
            $this->routes[$lastIndex]['name'] = $name;
        }
        return $this;
    }

    public function route(string $name, array $params = []): string
    {
        foreach ($this->routes as $route) {
            if ($route['name'] === $name) {
                $path = $route['path'];
                foreach ($params as $key => $value) {
                    $path = str_replace('{' . $key . '}', $value, $path);
                }
                return url($path);
            }
        }
        return url('/');
    }

    public function group(array $attributes, callable $callback): void
    {
        $prefix = $attributes['prefix'] ?? '';
        $middlewares = $attributes['middlewares'] ?? $attributes['middleware'] ?? [];

        if (is_string($middlewares)) {
            $middlewares = [$middlewares];
        }

        $this->groupStack[] = [
            'prefix' => $prefix,
            'middlewares' => $middlewares,
        ];

        $callback($this);

        array_pop($this->groupStack);
    }

    public function dispatch(): void
    {
        $method = $this->request->method();
        $uri = $this->request->path();
        $uri = '/' . trim($uri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchRoute($route['path'], $uri);

            if ($params !== null) {
                $this->request->setRouteParams($params);

                if (!$this->runMiddlewares($route['middlewares'])) {
                    return;
                }

                $this->executeHandler($route['handler']);
                return;
            }
        }

        $this->response->error404();
    }

    private function matchRoute(string $routePath, string $uri): ?array
    {
        $routePath = trim($routePath, '/');
        $uri = trim($uri, '/');

        if ($routePath === $uri) {
            return [];
        }

        $routeParts = explode('/', $routePath);
        $uriParts = explode('/', $uri);

        if (count($routeParts) !== count($uriParts)) {
            return null;
        }

        $params = [];

        foreach ($routeParts as $index => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $paramName = trim($part, '{}');
                $paramName = rtrim($paramName, '?');
                $params[$paramName] = $uriParts[$index];
            } elseif ($part !== $uriParts[$index]) {
                return null;
            }
        }

        return $params;
    }

    private function runMiddlewares(array $middlewares): bool
    {
        foreach ($middlewares as $middleware) {
            if (is_callable($middleware)) {
                $result = call_user_func($middleware, $this->request, $this->response);
                if ($result === false) {
                    return false;
                }
            } elseif (is_string($middleware) && class_exists($middleware)) {
                $instance = new $middleware();
                if (method_exists($instance, 'handle')) {
                    $result = $instance->handle($this->request, $this->response);
                    if ($result === false) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    private function executeHandler(mixed $handler): void
    {
        if (is_callable($handler)) {
            $result = call_user_func($handler, $this->request, $this->response);
            if (is_string($result)) {
                echo $result;
            }
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$controller, $method] = $handler;
            if (is_string($controller) && class_exists($controller)) {
                $instance = new $controller();
                if (method_exists($instance, $method)) {
                    call_user_func([$instance, $method], $this->request, $this->response);
                    return;
                }
            }
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$controller, $method] = explode('@', $handler);
            if (class_exists($controller)) {
                $instance = new $controller();
                if (method_exists($instance, $method)) {
                    call_user_func([$instance, $method], $this->request, $this->response);
                    return;
                }
            }
        }

        $this->response->error500('Handler de ruta no valido');
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function hasRoute(string $name): bool
    {
        foreach ($this->routes as $route) {
            if ($route['name'] === $name) {
                return true;
            }
        }
        return false;
    }
}