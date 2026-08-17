<?php

declare(strict_types=1);

class Router
{
    private array $routes = [];

    public function add(
        string $method,
        string $path,
        callable $action,
        array $middlewares = []
    ): void {
        $this->routes[] = [
            "method"      => $method,
            "path"        => $path,
            "action"      => $action,
            "middlewares" => $middlewares
        ];
    }

    public function dispatch(
        string $method,
        string $uri
    ): void {
        foreach ($this->routes as $route) {

            if ($route["method"] !== $method) {
                continue;
            }

            $pattern = preg_replace(
                '/\{[a-zA-Z_][a-zA-Z0-9_]*\}/',
                '([^/]+)',
                $route["path"]
            );

            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                $matches = array_map(function ($value) {
                    return is_numeric($value) ? $value + 0 : $value;
                }, $matches);

                foreach ($route["middlewares"] as $middleware) {
                    if (is_callable($middleware)) {
                        call_user_func($middleware);
                    }
                }

                call_user_func_array(
                    $route["action"],
                    $matches
                );

                return;
            }
        }

        http_response_code(404);
        echo json_encode([
            "erro" => "Rota não encontrada"
        ]);
    }
}