<?php

declare(strict_types=1);

/**
 * Regras de negócio do mecanismo de recuperação de senha.
 *
 * O fluxo segue as recomendações do OWASP Forgot Password Cheat Sheet:
 *
 *   1. Token aleatório de 256 bits, persistido apenas como resumo SHA-256.
 *   2. Uso único e prazo de validade curto.
 *   3. Resposta idêntica para e-mails existentes e inexistentes,
 *      incluindo o tempo de resposta, para impedir a enumeração de contas.
 *   4. Rate limiting por conta.
 *   5. Nenhuma autenticação automática após a redefinição.
 *   6. E-mail de confirmação depois da alteração, sem conter a senha.
 */
class RecuperacaoSenhaService
{
    /** Mensagem única devolvida ao solicitante, exista ou não a conta. */
    public const MENSAGEM_GENERICA = "Se o e-mail informado estiver cadastrado, enviaremos as instruções de redefinição de senha.";

    /** Mensagem única para qualquer falha de token, sem revelar a causa. */
    public const MENSAGEM_TOKEN_INVALIDO = "Token de recuperação inválido ou expirado.";

    public const MINUTOS_DE_VALIDADE_PADRAO = 30;
    public const MAXIMO_SOLICITACOES_PADRAO = 3;
    public const JANELA_RATE_LIMIT_PADRAO = 15;

    /**
     * Piso de tempo, em milissegundos, para a resposta da solicitação.
     * Sem ele, a diferença entre "achou a conta e enviou o e-mail" e
     * "não achou nada" seria mensurável e viraria um oráculo de
     * enumeração de contas.
     */
    private const TEMPO_MINIMO_DE_RESPOSTA_MS = 400;

    private RecuperacaoSenhaDAO $recuperacaoSenhaDAO;
    private UsuarioDAO $usuarioDAO;
    private EmailService $emailService;

    private int $minutosDeValidade;
    private int $maximoSolicitacoes;
    private int $janelaRateLimit;

    public function __construct(
        RecuperacaoSenhaDAO $recuperacaoSenhaDAO,
        UsuarioDAO $usuarioDAO,
        EmailService $emailService,
        ?array $configuracao = null
    ) {
        $this->recuperacaoSenhaDAO = $recuperacaoSenhaDAO;
        $this->usuarioDAO = $usuarioDAO;
        $this->emailService = $emailService;

        $configuracao = $configuracao ?? $_ENV;

        $this->minutosDeValidade = (int) ($configuracao["PASSWORD_RESET_TTL_MINUTES"] ?? self::MINUTOS_DE_VALIDADE_PADRAO);
        $this->maximoSolicitacoes = (int) ($configuracao["PASSWORD_RESET_MAX_REQUESTS"] ?? self::MAXIMO_SOLICITACOES_PADRAO);
        $this->janelaRateLimit = (int) ($configuracao["PASSWORD_RESET_WINDOW_MINUTES"] ?? self::JANELA_RATE_LIMIT_PADRAO);

        if ($this->minutosDeValidade <= 0) {
            $this->minutosDeValidade = self::MINUTOS_DE_VALIDADE_PADRAO;
        }
    }

    /**
     * Registra a solicitação e dispara o e-mail com o link.
     *
     * O método nunca informa se a conta existe: qualquer desfecho
     * produz a mesma resposta e um tempo de execução equivalente.
     */
    public function solicitar(string $correioEletronico, ?string $enderecoIp = null): void
    {
        $inicio = microtime(true);

        try {
            $correioEletronico = strtolower(trim($correioEletronico));

            if ($correioEletronico === "" || !filter_var($correioEletronico, FILTER_VALIDATE_EMAIL)) {
                return;
            }

            $usuario = $this->usuarioDAO->buscarPorCorreioEletronico($correioEletronico);

            if ($usuario === null) {
                return;
            }

            $idUsuario = (int) $usuario->getIdUsuario();

            if ($this->excedeuLimiteDeSolicitacoes($idUsuario)) {
                return;
            }

            $this->recuperacaoSenhaDAO->invalidarTokensDoUsuario($idUsuario);

            [$recuperacao, $token] = RecuperacaoSenha::emitir(
                $idUsuario,
                $this->minutosDeValidade,
                $enderecoIp
            );

            $this->recuperacaoSenhaDAO->salvar($recuperacao);

            $this->despacharEmailDeRecuperacao($usuario, $recuperacao, $token);
        } finally {
            $this->nivelarTempoDeResposta($inicio);
        }
    }

    /**
     * Confere se o token apresentado ainda pode ser usado.
     *
     * Serve tanto ao endpoint de validação, que permite ao frontend
     * decidir se exibe o formulário, quanto à própria redefinição.
     */
    public function validarToken(string $token): RecuperacaoSenha
    {
        $token = trim($token);

        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            throw new RegraDeNegocioException(self::MENSAGEM_TOKEN_INVALIDO);
        }

        $recuperacao = $this->recuperacaoSenhaDAO->buscarPorHashToken(
            RecuperacaoSenha::calcularHash($token)
        );

        if ($recuperacao === null || !$recuperacao->corresponde($token)) {
            throw new RegraDeNegocioException(self::MENSAGEM_TOKEN_INVALIDO);
        }

        if (!$recuperacao->estaValido()) {
            throw new RegraDeNegocioException(self::MENSAGEM_TOKEN_INVALIDO);
        }

        return $recuperacao;
    }

    /**
     * Consome o token e grava a nova senha.
     *
     * Não autentica o usuário automaticamente: ele volta à tela de
     * login e prova que conhece a senha recém-cadastrada.
     */
    public function redefinir(string $token, string $novaSenha, string $novaSenhaRepeticao): void
    {
        if (trim($novaSenha) !== trim($novaSenhaRepeticao)) {
            throw new RegraDeNegocioException("Senhas não conferem!");
        }

        $recuperacao = $this->validarToken($token);

        $usuario = $this->usuarioDAO->buscarPorId($recuperacao->getIdUsuario());

        if ($usuario === null) {
            throw new RegraDeNegocioException(self::MENSAGEM_TOKEN_INVALIDO);
        }

        // Reaproveita a política de senha do próprio modelo: a validação
        // lança DomainException e o hash sai pronto de getSenha().
        $usuario->setSenha($novaSenha);

        // Consome o token antes de gravar. O UPDATE condicional do DAO
        // garante que apenas uma requisição concorrente prossiga.
        if (!$this->recuperacaoSenhaDAO->marcarComoUtilizado((int) $recuperacao->getIdRecuperacaoSenha())) {
            throw new RegraDeNegocioException(self::MENSAGEM_TOKEN_INVALIDO);
        }

        $this->usuarioDAO->atualizarHashSenha($recuperacao->getIdUsuario(), $usuario->getSenha());

        $this->recuperacaoSenhaDAO->invalidarTokensDoUsuario($recuperacao->getIdUsuario());

        $this->despacharConfirmacao($usuario);
    }

    public function getMinutosDeValidade(): int
    {
        return $this->minutosDeValidade;
    }

    private function excedeuLimiteDeSolicitacoes(int $idUsuario): bool
    {
        if ($this->maximoSolicitacoes <= 0) {
            return false;
        }

        $solicitacoes = $this->recuperacaoSenhaDAO->contarSolicitacoesRecentes(
            $idUsuario,
            $this->janelaRateLimit
        );

        return $solicitacoes >= $this->maximoSolicitacoes;
    }

    /**
     * Uma falha de SMTP não pode virar resposta diferenciada, sob pena
     * de reintroduzir a enumeração de contas. O erro vai para o log do
     * PHP e o token é invalidado, evitando credencial pendente.
     */
    private function despacharEmailDeRecuperacao(
        Usuario $usuario,
        RecuperacaoSenha $recuperacao,
        string $token
    ): void {
        try {
            $this->emailService->enviarRecuperacaoSenha($usuario, $token, $this->minutosDeValidade);
        } catch (Throwable $e) {
            $this->recuperacaoSenhaDAO->marcarComoUtilizado((int) $recuperacao->getIdRecuperacaoSenha());

            error_log(sprintf(
                "[recuperacao-senha] Falha ao enviar e-mail para o usuário %d: %s",
                $usuario->getIdUsuario(),
                $e->getMessage()
            ));
        }
    }

    /**
     * A senha já foi trocada com sucesso: um problema no e-mail de
     * confirmação não deve reverter a operação nem devolver erro.
     */
    private function despacharConfirmacao(Usuario $usuario): void
    {
        try {
            $this->emailService->enviarConfirmacaoAlteracaoSenha($usuario);
        } catch (Throwable $e) {
            error_log(sprintf(
                "[recuperacao-senha] Falha ao enviar a confirmação para o usuário %d: %s",
                $usuario->getIdUsuario(),
                $e->getMessage()
            ));
        }
    }

    private function nivelarTempoDeResposta(float $inicio): void
    {
        $decorridoMs = (microtime(true) - $inicio) * 1000;
        $restanteMs = self::TEMPO_MINIMO_DE_RESPOSTA_MS - $decorridoMs;

        if ($restanteMs > 0) {
            usleep((int) round($restanteMs * 1000));
        }
    }
}
