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
        $metodosDoCaminho = [];

        foreach ($this->routes as $route) {

            $pattern = $this->compilar($route["path"]);

            if (!preg_match($pattern, $uri, $matches)) {
                continue;
            }

            if ($route["method"] !== $method) {
                $metodosDoCaminho[] = $route["method"];
                continue;
            }

            array_shift($matches);

            $matches = array_map(function ($value) {
                return is_numeric($value) ? $value + 0 : $value;
            }, $matches);

            try {
                foreach ($route["middlewares"] as $middleware) {
                    if (is_callable($middleware)) {
                        call_user_func($middleware);
                    }
                }

                call_user_func_array(
                    $route["action"],
                    $matches
                );
            } catch (Throwable $e) {
                /*
                 * Rede de segurança do dispatcher.
                 *
                 * Sem ela, qualquer erro escapado de um controller
                 * virava fatal do PHP: a resposta saía como HTML, com
                 * o caminho absoluto do arquivo no servidor, e o
                 * frontend recebia algo que nem era JSON para
                 * transformar em mensagem.
                 */
                error_log(sprintf(
                    "[Router] %s: %s em %s:%d (%s %s)",
                    get_class($e),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine(),
                    $method,
                    $uri
                ));

                http_response_code(500);
                header("Content-Type: application/json");
                echo json_encode([
                    "erro" => "Não foi possível concluir a operação. Tente novamente mais tarde."
                ]);
            }

            return;
        }

        header("Content-Type: application/json");

        if ($metodosDoCaminho !== []) {
            /*
             * O caminho existe, o verbo é que não.
             *
             * Era este o caso de GET /api/v1/usuarios/missoes: a rota
             * só existe em POST, e o GET acabava capturado por
             * /api/v1/usuarios/{idUsuario}. Agora a resposta diz
             * exatamente o que houve, e o cabeçalho Allow informa
             * quais verbos o caminho aceita.
             */
            $permitidos = array_values(array_unique($metodosDoCaminho));

            http_response_code(405);
            header("Allow: " . implode(", ", $permitidos));

            echo json_encode([
                "erro" => sprintf(
                    "Método %s não permitido para este recurso. Use: %s.",
                    $method,
                    implode(", ", $permitidos)
                )
            ]);

            return;
        }

        http_response_code(404);
        echo json_encode([
            "erro" => "Rota não encontrada"
        ]);
    }

    /**
     * Transforma o caminho declarado na rota em expressão regular.
     */
    private function compilar(string $path): string
    {
        $pattern = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            function (array $ocorrencia): string {
                $nome = $ocorrencia[1];

                return $this->ehIdentificador($nome)
                    ? '(\d+)'
                    : '([^/]+)';
            },
            $path
        );

        return '#^' . $pattern . '$#';
    }

    private function ehIdentificador(string $nome): bool
    {
        return strtolower($nome) === "id"
            || str_starts_with(strtolower($nome), "id");
    }
}
