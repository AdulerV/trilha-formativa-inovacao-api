<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Casamento de rotas.
 */
final class RoteamentoTest extends TestCase
{
    /**
     * Executa o dispatch capturando apenas o corpo da resposta.
     *
     * O manipulador de erros é trocado durante a chamada porque, sob
     * o PHPUnit em CLI, o cabeçalho já foi enviado pelo próprio
     * runner: header() e http_response_code() emitem warning, e o
     * conversor de warnings do PHPUnit transformaria isso em falha
     * de teste. O que está sob teste aqui é qual rota é escolhida e
     * o que ela devolve no corpo, não o envio de cabeçalhos.
     */
    private function despachar(
        Router $router,
        string $metodo,
        string $uri
    ): string {
        set_error_handler(static fn (): bool => true);
        ob_start();

        try {
            $router->dispatch($metodo, $uri);
        } finally {
            $corpo = (string) ob_get_clean();
            restore_error_handler();
        }

        return $corpo;
    }

    private function montarRouter(array &$chamadas): Router
    {
        $router = new Router();

        $router->add(
            "GET",
            "/api/v1/usuarios/{idUsuario}",
            function ($id) use (&$chamadas) {
                $chamadas[] = ["buscarPorId", $id];
            }
        );

        $router->add(
            "POST",
            "/api/v1/usuarios/missoes",
            function () use (&$chamadas) {
                $chamadas[] = ["salvarProgresso"];
            }
        );

        $router->add(
            "DELETE",
            "/api/v1/usuarios/{idUsuario}/alternativas/{idAlternativa}",
            function ($idUsuario, $idAlternativa) use (&$chamadas) {
                $chamadas[] = ["deletar", $idUsuario, $idAlternativa];
            }
        );

        return $router;
    }

    public function testSegmentoLiteralNaoEhCapturadoComoIdentificador(): void
    {
        $chamadas = [];
        $router = $this->montarRouter($chamadas);

        $corpo = $this->despachar($router, "GET", "/api/v1/usuarios/missoes");

        $this->assertSame(
            [],
            $chamadas,
            "buscarPorId não pode ser chamado com um segmento não numérico."
        );

        $resposta = json_decode($corpo, true);

        $this->assertIsArray($resposta);
        $this->assertArrayHasKey("erro", $resposta);
        $this->assertStringContainsString("POST", $resposta["erro"]);
    }

    public function testIdentificadorNumericoChegaComoInteiro(): void
    {
        $chamadas = [];
        $router = $this->montarRouter($chamadas);

        $this->despachar($router, "GET", "/api/v1/usuarios/12");

        $this->assertSame([["buscarPorId", 12]], $chamadas);
        $this->assertIsInt($chamadas[0][1]);
    }

    public function testRotaLiteralContinuaFuncionandoNoMetodoCerto(): void
    {
        $chamadas = [];
        $router = $this->montarRouter($chamadas);

        $this->despachar($router, "POST", "/api/v1/usuarios/missoes");

        $this->assertSame([["salvarProgresso"]], $chamadas);
    }

    public function testVariosIdentificadoresNaMesmaRota(): void
    {
        $chamadas = [];
        $router = $this->montarRouter($chamadas);

        $this->despachar(
            $router,
            "DELETE",
            "/api/v1/usuarios/3/alternativas/9"
        );

        $this->assertSame([["deletar", 3, 9]], $chamadas);
    }

    public function testCaminhoInexistenteContinuaDevolvendo404(): void
    {
        $chamadas = [];
        $router = $this->montarRouter($chamadas);

        $corpo = $this->despachar($router, "GET", "/api/v1/inexistente");

        $resposta = json_decode($corpo, true);

        $this->assertSame("Rota não encontrada", $resposta["erro"] ?? null);
    }

    /**
     * Erro escapado do controller vira JSON, e não fatal do PHP com o
     * caminho do arquivo no corpo da resposta.
     */
    public function testErroNoControllerVoltaComoJson(): void
    {
        $router = new Router();

        $router->add("GET", "/api/v1/explode", function () {
            throw new RuntimeException("detalhe interno do servidor");
        });

        $corpo = $this->despachar($router, "GET", "/api/v1/explode");

        $resposta = json_decode($corpo, true);

        $this->assertIsArray($resposta);
        $this->assertArrayHasKey("erro", $resposta);
        $this->assertStringNotContainsString(
            "detalhe interno do servidor",
            $corpo
        );
    }
}
