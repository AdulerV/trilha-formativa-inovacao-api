<?php

declare(strict_types=1);

/**
 * Representa uma solicitação de recuperação de senha.
 *
 * O token entregue ao usuário nunca é persistido: a entidade guarda
 * apenas o resumo SHA-256 dele (propriedade $hashToken). O token em
 * claro existe somente em memória, no instante da emissão, para ser
 * enviado por e-mail.
 */
class RecuperacaoSenha
{
    /** Tamanho, em bytes, do material aleatório do token. */
    public const TAMANHO_TOKEN_BYTES = 32;

    private ?int $idRecuperacaoSenha = null;
    private int $idUsuario;
    private string $hashToken;
    private DateTime $dataCriacao;
    private DateTime $dataExpiracao;
    private ?DateTime $dataUtilizacao = null;
    private ?string $enderecoIp = null;

    public function __construct(
        ?int $idRecuperacaoSenha,
        int $idUsuario,
        string $hashToken,
        DateTime $dataCriacao,
        DateTime $dataExpiracao,
        ?DateTime $dataUtilizacao = null,
        ?string $enderecoIp = null
    ) {
        $this->setIdRecuperacaoSenha($idRecuperacaoSenha);
        $this->setIdUsuario($idUsuario);
        $this->setHashToken($hashToken);
        $this->setDataCriacao($dataCriacao);
        $this->setDataExpiracao($dataExpiracao);
        $this->setDataUtilizacao($dataUtilizacao);
        $this->setEnderecoIp($enderecoIp);
    }

    /**
     * Gera um token criptograficamente seguro de 64 caracteres
     * hexadecimais (256 bits de entropia).
     */
    public static function gerarToken(): string
    {
        return bin2hex(random_bytes(self::TAMANHO_TOKEN_BYTES));
    }

    /**
     * Calcula o resumo que será persistido.
     *
     * SHA-256 é suficiente aqui — e preferível a bcrypt — porque o
     * token é aleatório e de altíssima entropia: não existe espaço de
     * busca a ser protegido por um hash lento.
     */
    public static function calcularHash(string $token): string
    {
        return hash("sha256", $token);
    }

    /**
     * Fabrica uma solicitação já com token gerado.
     *
     * @return array{0: RecuperacaoSenha, 1: string} A entidade e o token em claro.
     */
    public static function emitir(
        int $idUsuario,
        int $minutosDeValidade,
        ?string $enderecoIp = null
    ): array {
        if ($minutosDeValidade <= 0) {
            throw new DomainException("Tempo de validade do token deve ser positivo!");
        }

        $token = self::gerarToken();

        $agora = new DateTime();
        $expiracao = (clone $agora)->modify("+{$minutosDeValidade} minutes");

        $recuperacao = new self(
            null,
            $idUsuario,
            self::calcularHash($token),
            $agora,
            $expiracao,
            null,
            $enderecoIp
        );

        return [$recuperacao, $token];
    }

    public function estaExpirado(?DateTime $referencia = null): bool
    {
        $referencia = $referencia ?? new DateTime();

        return $this->dataExpiracao <= $referencia;
    }

    public function foiUtilizado(): bool
    {
        return $this->dataUtilizacao !== null;
    }

    public function estaValido(?DateTime $referencia = null): bool
    {
        return !$this->foiUtilizado() && !$this->estaExpirado($referencia);
    }

    /**
     * Confere, em tempo constante, se o token apresentado corresponde
     * ao resumo armazenado.
     */
    public function corresponde(string $token): bool
    {
        return hash_equals($this->hashToken, self::calcularHash($token));
    }

    public function getIdRecuperacaoSenha(): ?int
    {
        return $this->idRecuperacaoSenha;
    }

    public function setIdRecuperacaoSenha(?int $idRecuperacaoSenha): self
    {
        if ($this->idRecuperacaoSenha !== null && $idRecuperacaoSenha !== $this->idRecuperacaoSenha) {
            throw new Exception("ID já definido!");
        }

        $this->idRecuperacaoSenha = $idRecuperacaoSenha;

        return $this;
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function setIdUsuario(int $idUsuario): self
    {
        if ($idUsuario <= 0) {
            throw new DomainException("Usuário inválido para recuperação de senha!");
        }

        $this->idUsuario = $idUsuario;

        return $this;
    }

    public function getHashToken(): string
    {
        return $this->hashToken;
    }

    public function setHashToken(string $hashToken): self
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $hashToken)) {
            throw new DomainException("Hash de token inválido!");
        }

        $this->hashToken = $hashToken;

        return $this;
    }

    public function getDataCriacao(): DateTime
    {
        return $this->dataCriacao;
    }

    public function setDataCriacao(DateTime $dataCriacao): self
    {
        $this->dataCriacao = $dataCriacao;

        return $this;
    }

    public function getDataExpiracao(): DateTime
    {
        return $this->dataExpiracao;
    }

    public function setDataExpiracao(DateTime $dataExpiracao): self
    {
        $this->dataExpiracao = $dataExpiracao;

        return $this;
    }

    public function getDataUtilizacao(): ?DateTime
    {
        return $this->dataUtilizacao;
    }

    public function setDataUtilizacao(?DateTime $dataUtilizacao): self
    {
        $this->dataUtilizacao = $dataUtilizacao;

        return $this;
    }

    public function getEnderecoIp(): ?string
    {
        return $this->enderecoIp;
    }

    public function setEnderecoIp(?string $enderecoIp): self
    {
        if ($enderecoIp !== null && !filter_var($enderecoIp, FILTER_VALIDATE_IP)) {
            $enderecoIp = null;
        }

        $this->enderecoIp = $enderecoIp;

        return $this;
    }
}
