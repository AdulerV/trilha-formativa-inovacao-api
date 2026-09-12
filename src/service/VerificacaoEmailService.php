<?php

declare(strict_types=1);

/**
 * Regras da verificação de e-mail anterior ao cadastro.
 *
 * O fluxo espelha o da recuperação de senha, com uma diferença que
 * muda tudo: o segredo enviado tem seis dígitos, não 256 bits.
 *
 * Um código de seis dígitos tem 10^6 combinações. Sem proteção, um
 * atacante que conhece o e-mail alvo acerta por força bruta em poucos
 * minutos de requisições. São três controles que tornam o número
 * curto aceitável, e nenhum deles é dispensável:
 *
 *   1. limite de tentativas por código, que queima a verificação após
 *      N erros e reduz a chance de acerto a N/10^6;
 *   2. prazo de validade curto, que fecha a janela de ataque;
 *   3. limite de emissões por e-mail, que impede o atacante de
 *      renovar o código indefinidamente para recuperar tentativas.
 *
 * Confirmado o código, a API devolve um comprovante de 256 bits. É ele,
 * e não o código, que autoriza a criação da conta — assim o segredo
 * fraco vale por uma janela curta e o segredo forte atravessa o resto
 * do fluxo.
 */
class VerificacaoEmailService
{
    public const MENSAGEM_CODIGO_INVALIDO = "Código de verificação inválido ou expirado.";
    public const MENSAGEM_EMAIL_EM_USO = "Este e-mail já está cadastrado.";
    public const MENSAGEM_LIMITE_SOLICITACOES = "Muitas solicitações para este e-mail. Aguarde alguns minutos antes de tentar novamente.";
    public const MENSAGEM_COMPROVANTE_INVALIDO = "Verificação de e-mail ausente ou expirada. Solicite um novo código.";

    public const MINUTOS_DE_VALIDADE_PADRAO = 15;
    public const MAXIMO_TENTATIVAS_PADRAO = 5;
    public const MAXIMO_SOLICITACOES_PADRAO = 3;
    public const JANELA_RATE_LIMIT_PADRAO = 15;
    public const MINUTOS_COMPROVANTE_PADRAO = 30;

    private VerificacaoEmailDAO $verificacaoEmailDAO;
    private UsuarioDAO $usuarioDAO;
    private EmailService $emailService;

    private int $minutosDeValidade;
    private int $maximoTentativas;
    private int $maximoSolicitacoes;
    private int $janelaRateLimit;
    private int $minutosComprovante;

    public function __construct(
        VerificacaoEmailDAO $verificacaoEmailDAO,
        UsuarioDAO $usuarioDAO,
        EmailService $emailService,
        ?array $configuracao = null
    ) {
        $this->verificacaoEmailDAO = $verificacaoEmailDAO;
        $this->usuarioDAO = $usuarioDAO;
        $this->emailService = $emailService;

        $configuracao = $configuracao ?? $_ENV;

        $this->minutosDeValidade = (int) ($configuracao["EMAIL_VERIFICATION_TTL_MINUTES"]
            ?? self::MINUTOS_DE_VALIDADE_PADRAO);
        $this->maximoTentativas = (int) ($configuracao["EMAIL_VERIFICATION_MAX_ATTEMPTS"]
            ?? self::MAXIMO_TENTATIVAS_PADRAO);
        $this->maximoSolicitacoes = (int) ($configuracao["EMAIL_VERIFICATION_MAX_REQUESTS"]
            ?? self::MAXIMO_SOLICITACOES_PADRAO);
        $this->janelaRateLimit = (int) ($configuracao["EMAIL_VERIFICATION_WINDOW_MINUTES"]
            ?? self::JANELA_RATE_LIMIT_PADRAO);
        $this->minutosComprovante = (int) ($configuracao["EMAIL_VERIFICATION_PROOF_TTL_MINUTES"]
            ?? self::MINUTOS_COMPROVANTE_PADRAO);

        if ($this->minutosDeValidade <= 0) {
            $this->minutosDeValidade = self::MINUTOS_DE_VALIDADE_PADRAO;
        }

        if ($this->minutosComprovante <= 0) {
            $this->minutosComprovante = self::MINUTOS_COMPROVANTE_PADRAO;
        }
    }

    /**
     * Emite um código e o envia ao endereço informado.
     *
     * Ao contrário da recuperação de senha, aqui a API responde de
     * forma diferente quando o e-mail já está cadastrado. Isso não
     * enfraquece nada: o próprio POST /usuarios já recusa duplicidade
     * com "Email já utilizado!", então esconder a informação neste
     * endpoint não protegeria segredo nenhum e só deixaria o usuário
     * sem saber por que o cadastro não avança.
     */
    public function solicitar(string $correioEletronico, ?string $enderecoIp = null): void
    {
        $correioEletronico = strtolower(trim($correioEletronico));

        if ($correioEletronico === "" || !filter_var($correioEletronico, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException("E-mail inválido!");
        }

        if ($this->usuarioDAO->verificarCorreioEletronicoExiste($correioEletronico)) {
            throw new RegraDeNegocioException(self::MENSAGEM_EMAIL_EM_USO);
        }

        if ($this->excedeuLimiteDeSolicitacoes($correioEletronico)) {
            throw new RegraDeNegocioException(self::MENSAGEM_LIMITE_SOLICITACOES);
        }

        $this->verificacaoEmailDAO->invalidarPendentesPorEmail($correioEletronico);

        [$verificacao, $codigo] = VerificacaoEmail::emitir(
            $correioEletronico,
            $this->minutosDeValidade,
            $enderecoIp
        );

        $this->verificacaoEmailDAO->salvar($verificacao);

        try {
            $this->emailService->enviarCodigoVerificacao(
                $correioEletronico,
                $codigo,
                $this->minutosDeValidade
            );
        } catch (Throwable $e) {
            // Sem e-mail entregue não há como o usuário prosseguir, e
            // deixar o registro em aberto só consumiria a cota dele.
            $this->verificacaoEmailDAO->marcarComoConsumido(
                (int) $verificacao->getIdVerificacaoEmail()
            );

            error_log(sprintf(
                "[verificacao-email] Falha ao enviar o código para %s: %s",
                $correioEletronico,
                $e->getMessage()
            ));

            throw new RegraDeNegocioException(
                "Não foi possível enviar o código de verificação. Tente novamente em instantes."
            );
        }
    }

    /**
     * Confere o código e devolve o comprovante que autoriza o cadastro.
     *
     * Toda falha responde com a mesma mensagem, sem dizer se o código
     * estava errado, expirado ou se as tentativas acabaram. Informar a
     * causa ajudaria o atacante a saber quando vale a pena recomeçar.
     */
    public function confirmar(string $correioEletronico, string $codigo): string
    {
        $correioEletronico = strtolower(trim($correioEletronico));
        $codigo = trim($codigo);

        if ($correioEletronico === "" || !filter_var($correioEletronico, FILTER_VALIDATE_EMAIL)) {
            throw new RegraDeNegocioException(self::MENSAGEM_CODIGO_INVALIDO);
        }

        if (!preg_match('/^\d{' . VerificacaoEmail::TAMANHO_CODIGO . '}$/', $codigo)) {
            throw new RegraDeNegocioException(self::MENSAGEM_CODIGO_INVALIDO);
        }

        $verificacao = $this->verificacaoEmailDAO->buscarPendentePorEmail($correioEletronico);

        if ($verificacao === null || !$verificacao->aceitaCodigo($this->maximoTentativas)) {
            throw new RegraDeNegocioException(self::MENSAGEM_CODIGO_INVALIDO);
        }

        if (!$verificacao->codigoConfere($codigo)) {
            // A tentativa é contabilizada antes de responder: é este
            // contador que transforma 10^6 possibilidades em um número
            // de chances que cabe nos dedos.
            $this->verificacaoEmailDAO->registrarTentativa(
                (int) $verificacao->getIdVerificacaoEmail()
            );

            throw new RegraDeNegocioException(self::MENSAGEM_CODIGO_INVALIDO);
        }

        $comprovante = VerificacaoEmail::gerarComprovante();

        $expiracao = (new DateTime())->modify("+{$this->minutosComprovante} minutes");

        $confirmou = $this->verificacaoEmailDAO->marcarComoVerificado(
            (int) $verificacao->getIdVerificacaoEmail(),
            VerificacaoEmail::calcularHash($comprovante),
            $expiracao
        );

        if (!$confirmou) {
            throw new RegraDeNegocioException(self::MENSAGEM_CODIGO_INVALIDO);
        }

        return $comprovante;
    }

    /**
     * Troca o comprovante pela autorização de criar a conta.
     *
     * Chamado pelo UsuarioService dentro do cadastro. O consumo é
     * atômico, então o mesmo comprovante não cria duas contas, e o
     * e-mail do comprovante precisa ser exatamente o do cadastro — sem
     * essa conferência alguém verificaria o próprio endereço e
     * cadastraria outro.
     */
    public function consumirComprovante(string $correioEletronico, ?string $comprovante): void
    {
        $correioEletronico = strtolower(trim($correioEletronico));
        $comprovante = trim((string) $comprovante);

        if (!preg_match('/^[a-f0-9]{64}$/', $comprovante)) {
            throw new RegraDeNegocioException(self::MENSAGEM_COMPROVANTE_INVALIDO);
        }

        $verificacao = $this->verificacaoEmailDAO->buscarPorHashComprovante(
            VerificacaoEmail::calcularHash($comprovante)
        );

        if ($verificacao === null || !$verificacao->comprovanteConfere($comprovante)) {
            throw new RegraDeNegocioException(self::MENSAGEM_COMPROVANTE_INVALIDO);
        }

        if ($verificacao->getCorreioEletronico() !== $correioEletronico) {
            throw new RegraDeNegocioException(self::MENSAGEM_COMPROVANTE_INVALIDO);
        }

        if (!$verificacao->comprovanteEstaValido()) {
            throw new RegraDeNegocioException(self::MENSAGEM_COMPROVANTE_INVALIDO);
        }

        $consumiu = $this->verificacaoEmailDAO->marcarComoConsumido(
            (int) $verificacao->getIdVerificacaoEmail()
        );

        if (!$consumiu) {
            throw new RegraDeNegocioException(self::MENSAGEM_COMPROVANTE_INVALIDO);
        }
    }

    public function getMinutosDeValidade(): int
    {
        return $this->minutosDeValidade;
    }

    public function getMinutosComprovante(): int
    {
        return $this->minutosComprovante;
    }

    private function excedeuLimiteDeSolicitacoes(string $correioEletronico): bool
    {
        if ($this->maximoSolicitacoes <= 0) {
            return false;
        }

        $solicitacoes = $this->verificacaoEmailDAO->contarSolicitacoesRecentes(
            $correioEletronico,
            $this->janelaRateLimit
        );

        return $solicitacoes >= $this->maximoSolicitacoes;
    }
}
