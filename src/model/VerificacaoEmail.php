<?php

declare(strict_types=1);

class VerificacaoEmail
{
    public const TAMANHO_CODIGO = 6;

    public const TAMANHO_COMPROVANTE_BYTES = 32;

    private ?int $idVerificacaoEmail = null;
    private string $correioEletronico;
    private string $hashCodigo;
    private ?string $hashComprovante = null;
    private int $tentativas = 0;
    private DateTime $dataCriacao;
    private DateTime $dataExpiracao;
    private ?DateTime $dataVerificacao = null;
    private ?DateTime $dataExpiracaoComprovante = null;
    private ?DateTime $dataConsumo = null;
    private ?string $enderecoIp = null;

    public function __construct(
        ?int $idVerificacaoEmail,
        string $correioEletronico,
        string $hashCodigo,
        DateTime $dataCriacao,
        DateTime $dataExpiracao,
        int $tentativas = 0,
        ?string $hashComprovante = null,
        ?DateTime $dataVerificacao = null,
        ?DateTime $dataExpiracaoComprovante = null,
        ?DateTime $dataConsumo = null,
        ?string $enderecoIp = null
    ) {
        $this->setIdVerificacaoEmail($idVerificacaoEmail);
        $this->setCorreioEletronico($correioEletronico);
        $this->setHashCodigo($hashCodigo);
        $this->setDataCriacao($dataCriacao);
        $this->setDataExpiracao($dataExpiracao);
        $this->setTentativas($tentativas);
        $this->setHashComprovante($hashComprovante);
        $this->setDataVerificacao($dataVerificacao);
        $this->setDataExpiracaoComprovante($dataExpiracaoComprovante);
        $this->setDataConsumo($dataConsumo);
        $this->setEnderecoIp($enderecoIp);
    }

    public static function gerarCodigo(): string
    {
        $maximo = (10 ** self::TAMANHO_CODIGO) - 1;

        return str_pad(
            (string) random_int(0, $maximo),
            self::TAMANHO_CODIGO,
            "0",
            STR_PAD_LEFT
        );
    }

    public static function gerarComprovante(): string
    {
        return bin2hex(random_bytes(self::TAMANHO_COMPROVANTE_BYTES));
    }

    public static function calcularHash(string $valor): string
    {
        return hash("sha256", $valor);
    }

    /**
     * Fabrica uma verificação já com o código gerado.
     *
     * @return array{0: VerificacaoEmail, 1: string} A entidade e o código em claro.
     */
    public static function emitir(
        string $correioEletronico,
        int $minutosDeValidade,
        ?string $enderecoIp = null
    ): array {
        if ($minutosDeValidade <= 0) {
            throw new DomainException("Tempo de validade do código deve ser positivo!");
        }

        $codigo = self::gerarCodigo();

        $agora = new DateTime();
        $expiracao = (clone $agora)->modify("+{$minutosDeValidade} minutes");

        $verificacao = new self(
            null,
            $correioEletronico,
            self::calcularHash($codigo),
            $agora,
            $expiracao,
            0,
            null,
            null,
            null,
            null,
            $enderecoIp
        );

        return [$verificacao, $codigo];
    }

    public function estaExpirado(?DateTime $referencia = null): bool
    {
        $referencia = $referencia ?? new DateTime();

        return $this->dataExpiracao <= $referencia;
    }

    public function foiVerificado(): bool
    {
        return $this->dataVerificacao !== null;
    }

    public function foiConsumido(): bool
    {
        return $this->dataConsumo !== null;
    }

    public function excedeuTentativas(int $maximo): bool
    {
        return $maximo > 0 && $this->tentativas >= $maximo;
    }

    public function aceitaCodigo(int $maximoTentativas, ?DateTime $referencia = null): bool
    {
        return !$this->foiConsumido()
            && !$this->foiVerificado()
            && !$this->estaExpirado($referencia)
            && !$this->excedeuTentativas($maximoTentativas);
    }

    public function comprovanteEstaValido(?DateTime $referencia = null): bool
    {
        $referencia = $referencia ?? new DateTime();

        return $this->foiVerificado()
            && !$this->foiConsumido()
            && $this->hashComprovante !== null
            && $this->dataExpiracaoComprovante !== null
            && $this->dataExpiracaoComprovante > $referencia;
    }

    public function codigoConfere(string $codigo): bool
    {
        return hash_equals($this->hashCodigo, self::calcularHash(trim($codigo)));
    }

    public function comprovanteConfere(string $comprovante): bool
    {
        if ($this->hashComprovante === null) {
            return false;
        }

        return hash_equals($this->hashComprovante, self::calcularHash(trim($comprovante)));
    }

    public function getIdVerificacaoEmail(): ?int
    {
        return $this->idVerificacaoEmail;
    }

    public function setIdVerificacaoEmail(?int $idVerificacaoEmail): self
    {
        if ($this->idVerificacaoEmail !== null && $idVerificacaoEmail !== $this->idVerificacaoEmail) {
            throw new Exception("ID já definido!");
        }

        $this->idVerificacaoEmail = $idVerificacaoEmail;

        return $this;
    }

    public function getCorreioEletronico(): string
    {
        return $this->correioEletronico;
    }

    public function setCorreioEletronico(string $correioEletronico): self
    {
        $correioEletronico = strtolower(trim($correioEletronico));

        if (!filter_var($correioEletronico, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException("E-mail inválido!");
        }

        $this->correioEletronico = $correioEletronico;

        return $this;
    }

    public function getHashCodigo(): string
    {
        return $this->hashCodigo;
    }

    public function setHashCodigo(string $hashCodigo): self
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $hashCodigo)) {
            throw new DomainException("Hash de código inválido!");
        }

        $this->hashCodigo = $hashCodigo;

        return $this;
    }

    public function getHashComprovante(): ?string
    {
        return $this->hashComprovante;
    }

    public function setHashComprovante(?string $hashComprovante): self
    {
        if ($hashComprovante !== null && !preg_match('/^[a-f0-9]{64}$/', $hashComprovante)) {
            throw new DomainException("Hash de comprovante inválido!");
        }

        $this->hashComprovante = $hashComprovante;

        return $this;
    }

    public function getTentativas(): int
    {
        return $this->tentativas;
    }

    public function setTentativas(int $tentativas): self
    {
        if ($tentativas < 0) {
            throw new DomainException("Número de tentativas inválido!");
        }

        $this->tentativas = $tentativas;

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

    public function getDataVerificacao(): ?DateTime
    {
        return $this->dataVerificacao;
    }

    public function setDataVerificacao(?DateTime $dataVerificacao): self
    {
        $this->dataVerificacao = $dataVerificacao;

        return $this;
    }

    public function getDataExpiracaoComprovante(): ?DateTime
    {
        return $this->dataExpiracaoComprovante;
    }

    public function setDataExpiracaoComprovante(?DateTime $dataExpiracaoComprovante): self
    {
        $this->dataExpiracaoComprovante = $dataExpiracaoComprovante;

        return $this;
    }

    public function getDataConsumo(): ?DateTime
    {
        return $this->dataConsumo;
    }

    public function setDataConsumo(?DateTime $dataConsumo): self
    {
        $this->dataConsumo = $dataConsumo;

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
