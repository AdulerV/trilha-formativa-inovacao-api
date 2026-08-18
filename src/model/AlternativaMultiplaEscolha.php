<?php

declare(strict_types=1);

class AlternativaMultiplaEscolha extends Alternativa
{
    private string $tipoMultiplaEscolha;
    private bool $correta;

    public const SUBTIPO_VERDADEIRO_FALSO = 'verdadeiro_falso';
    public const SUBTIPO_MULTIPLA_CORRETA = 'multipla_correta';
    public const SUBTIPO_MULTIPLA_ESCOLHA = 'multipla_escolha';

    public function __construct(
        ?int $idAlternativa,
        string $texto,
        bool $correta,
        string $tipoMultiplaEscolha
    ) {
        parent::__construct(
            $idAlternativa,
            $texto,
            self::TIPO_MULTIPLA_ESCOLHA
        );

        $this->setCorreta($correta);
        $this->setTipoMultiplaEscolha($tipoMultiplaEscolha);
    }

    public function getTipoMultiplaEscolha(): string
    {
        return $this->tipoMultiplaEscolha;
    }

    public function setTipoMultiplaEscolha(string $tipoMultiplaEscolha): self
    {
        $subtiposValidos = [
            self::SUBTIPO_VERDADEIRO_FALSO,
            self::SUBTIPO_MULTIPLA_CORRETA,
            self::SUBTIPO_MULTIPLA_ESCOLHA
        ];

        if (!in_array($tipoMultiplaEscolha, $subtiposValidos, true)) {
            throw new DomainException(
                "Subtipo de multipla escolha invalido! Use: " . implode(', ', $subtiposValidos)
            );
        }

        $this->tipoMultiplaEscolha = $tipoMultiplaEscolha;
        return $this;
    }

    public function isCorreta(): bool
    {
        return $this->correta;
    }

    public function setCorreta(bool $correta): self
    {
        $this->correta = $correta;

        return $this;
    }
}
