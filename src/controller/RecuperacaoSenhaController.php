<?php

class RecuperacaoSenhaController
{
    private RecuperacaoSenhaService $service;

    public function __construct(RecuperacaoSenhaService $service)
    {
        $this->service = $service;
    }

    /**
     * POST /api/v1/recuperacao-senha/solicitar
     *
     * Sempre responde 200 com a mesma mensagem. É o comportamento
     * esperado: qualquer variação revelaria quais e-mails estão
     * cadastrados na plataforma.
     */
    public function solicitar(): void
    {
        try {
            $dados = Request::body();

            $correioEletronico = (string) ($dados["correioEletronico"] ?? $dados["email"] ?? "");

            $this->service->solicitar($correioEletronico, $this->descobrirEnderecoIp());

            Response::json([
                "mensagem" => RecuperacaoSenhaService::MENSAGEM_GENERICA
            ]);
        } catch (Exception $e) {
            error_log("[recuperacao-senha] Erro na solicitação: " . $e->getMessage());

            // Mesma resposta do caminho feliz, pelo mesmo motivo.
            Response::json([
                "mensagem" => RecuperacaoSenhaService::MENSAGEM_GENERICA
            ]);
        }
    }

    /**
     * GET /api/v1/recuperacao-senha/validar?token=...
     *
     * Permite ao frontend decidir entre exibir o formulário de nova
     * senha ou a tela de "link expirado", sem gastar o token.
     */
    public function validar(): void
    {
        try {
            $token = (string) (Request::query("token") ?? "");

            $recuperacao = $this->service->validarToken($token);

            Response::json([
                "valido" => true,
                "expiraEm" => $recuperacao->getDataExpiracao()->format(DateTime::ATOM)
            ]);
        } catch (RegraDeNegocioException $e) {
            Response::json([
                "valido" => false,
                "erro" => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            /*
             * A mensagem genérica protege o usuário, mas o motivo
             * precisa ficar registrado: era exatamente essa perda
             * de informação que tornava o 500 impossível de
             * diagnosticar.
             */
            error_log(sprintf(
                "[RecuperacaoSenha] %s: %s em %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * POST /api/v1/recuperacao-senha/redefinir
     */
    public function redefinir(): void
    {
        try {
            $dados = Request::body();

            $token = (string) ($dados["token"] ?? "");
            $novaSenha = (string) ($dados["novaSenha"] ?? "");
            $novaSenhaRepeticao = (string) ($dados["novaSenhaRepeticao"] ?? "");

            $this->service->redefinir($token, $novaSenha, $novaSenhaRepeticao);

            Response::json([
                "mensagem" => "Senha redefinida com sucesso! Faça login com a nova senha."
            ]);
        } catch (DomainException $e) {
            Response::error($e->getMessage(), 400);
        } catch (RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 400);
        } catch (Exception $e) {
            error_log("[recuperacao-senha] Erro na redefinição: " . $e->getMessage());

            Response::error($e->getMessage(), 400);
        }
    }

    private function descobrirEnderecoIp(): ?string
    {
        $enderecoIp = $_SERVER["REMOTE_ADDR"] ?? null;

        if ($enderecoIp === null || !filter_var($enderecoIp, FILTER_VALIDATE_IP)) {
            return null;
        }

        return $enderecoIp;
    }
}
