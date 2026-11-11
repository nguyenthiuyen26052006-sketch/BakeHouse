<?php

declare(strict_types=1);

namespace App\Core;

use App\Middleware\MiddlewareInterface;

final class Router
{
    /** @var array<int, array{method: string, pattern: string, handler: array|callable, middleware: array}> */
    private array $routes = [];
    private string $prefix = '';
    private array $groupMiddleware = [];

    public function get(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function put(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->add('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }

    /**
     * @param callable(Router): void $callback
     */
    public function group(string $prefix, array $middleware, callable $callback): void
    {
        $previousPrefix = $this->prefix;
        $previousMiddleware = $this->groupMiddleware;

        $this->prefix .= $prefix;
        $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);
        $callback($this);

        $this->prefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    public function dispatch(Request $request): Response
    {
        $pathMatched = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route['pattern'], $request->getPath(), $matches)) {
                continue;
            }
            $pathMatched = true;
            if ($route['method'] !== $request->getMethod()) {
                continue;
            }

            foreach ($route['middleware'] as $middlewareClass) {
                /** @var MiddlewareInterface $middleware */
                $middleware = is_string($middlewareClass) ? new $middlewareClass() : $middlewareClass;
                $response = $middleware->handle($request);
                if ($response !== null) {
                    return $response;
                }
            }

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            return $this->invoke($route['handler'], $request, $params);
        }

        return ErrorHandler::render($pathMatched ? 405 : 404, $request);
    }

    private function add(string $method, string $path, array|callable $handler, array $middleware): void
    {
        $fullPath = '/' . trim($this->prefix . '/' . trim($path, '/'), '/');
        $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $fullPath);

        $this->routes[] = [
            'method' => $method,
            'pattern' => '#^' . $pattern . '$#u',
            'handler' => $handler,
            'middleware' => array_merge($this->groupMiddleware, $middleware),
        ];
    }

    private function invoke(array|callable $handler, Request $request, array $params): Response
    {
        if (is_array($handler)) {
            [$class, $action] = $handler;
            $result = (new $class())->$action($request, ...array_values($params));
        } else {
            $result = $handler($request, ...array_values($params));
        }

        if ($result instanceof Response) {
            return $result;
        }

        return Response::html((string) $result);
    }
}
