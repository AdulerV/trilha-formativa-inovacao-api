<?php

class VerificacaoEmailController
{
    private VerificacaoEmailService $service;

    public function __construct(VerificacaoEmailService $service)
    {
        $this->service = $service;
    }

    /**
     * POST /api/v1/verificacao-email/solicitar
     *
     * Primeira etapa do cadastro: o frontend envia o e-mail digitado no
     * formulário e a API dispara o código de seis dígitos.
     */
    public function solicitar(): void
    {
        try {
            $dados = Request::body();

            $correioEletronico = (string) ($dados["correioEletronico"] ?? $dados["email"] ?? "");

            $this->service->solicitar($correioEletronico, $this->descobrirEnderecoIp());

            Response::json([
                "mensagem" => "Código de verificação enviado para o e-mail informado.",
                "expiraEmMinutos" => $this->service->getMinutosDeValidade()
            ]);
        } catch (DomainException | RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 400);
        } catch (Throwable $e) {
            error_log("[verificacao-email] Erro na solicitação: " . $e->getMessage());

            Response::error("Erro interno", 500);
        }
    }

    /**
     * POST /api/v1/verificacao-email/confirmar
     *
     * Segunda etapa: o usuário digita o código recebido. Em caso de
     * sucesso devolvemos o comprovante, que o frontend guarda e envia
     * junto do POST /usuarios.
     */
    public function confirmar(): void
    {
        try {
            $dados = Request::body();

            $correioEletronico = (string) ($dados["correioEletronico"] ?? $dados["email"] ?? "");
            $codigo = (string) ($dados["codigo"] ?? "");

            $comprovante = $this->service->confirmar($correioEletronico, $codigo);

            Response::json([
                "mensagem" => "E-mail verificado com sucesso!",
                "comprovanteVerificacao" => $comprovante,
                "expiraEmMinutos" => $this->service->getMinutosComprovante()
            ]);
        } catch (RegraDeNegocioException $e) {
            Response::error($e->getMessage(), 400);
        } catch (Throwable $e) {
            error_log("[verificacao-email] Erro na confirmação: " . $e->getMessage());

            Response::error("Erro interno", 500);
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
