<?php
declare(strict_types=1);

/**
 * Minimal pattern router: {name} segments become named parameters.
 */
final class Router
{
    /** @var list<array{method: string, regex: string, handler: callable}> */
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, callable $handler): void
    {
        if ($pattern === '/') {
            $regex = '#^/?$#u';
        } else {
            $compiled = preg_replace('#\{([a-z_]+)\}#', '(?P<$1>[^/]+)', $pattern) ?? $pattern;
            $regex = '#^' . $compiled . '/?$#u';
        }

        $this->routes[] = [
            'method' => $method,
            'regex' => $regex,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $path): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['regex'], $path, $matches) === 1) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = rawurldecode((string) $value);
                    }
                }

                ($route['handler'])($params);
                return;
            }
        }

        not_found();
    }
}
