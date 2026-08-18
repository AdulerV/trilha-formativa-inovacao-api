<?php

declare(strict_types=1);

class Alternativa
{
    private ?int $idAlternativa = null;
    private string $texto;
    private string $tipoAlternativa;
    private ?int $idQuestao = null;

    public const TIPO_ORDENACAO = 'ordenacao';
    public const TIPO_ASSOCIACAO = 'associacao';
    public const TIPO_MULTIPLA_ESCOLHA = 'multipla_escolha';

    public function __construct(
        ?int $idAlternativa,
        string $texto,
        string $tipoAlternativa
    ) {
        $this->setIdAlternativa($idAlternativa);
        $this->setTexto($texto);
        $this->setTipoAlternativa($tipoAlternativa);
    }

    public function getIdAlternativa(): ?int
    {
        return $this->idAlternativa;
    }

    public function setIdAlternativa(?int $idAlternativa): self
    {
        if ($this->idAlternativa !== null && $idAlternativa !== $this->idAlternativa) {
            throw new Exception("ID já definido!");
        }

        $this->idAlternativa = $idAlternativa;
        return $this;
    }

    public function getTexto(): string
    {
        return $this->texto;
    }

    public function setTexto(string $texto): self
    {
        $texto = trim($texto);

        if ($texto === "") {
            throw new DomainException("A alternativa precisa conter um texto!");
        }

        $this->texto = $texto;
        return $this;
    }

    public function getTipoAlternativa(): string
    {
        return $this->tipoAlternativa;
    }

    public function setTipoAlternativa(string $tipoAlternativa): self
    {
        $tiposValidos = [
            self::TIPO_ORDENACAO,
            self::TIPO_ASSOCIACAO,
            self::TIPO_MULTIPLA_ESCOLHA
        ];

        if (!in_array($tipoAlternativa, $tiposValidos, true)) {
            throw new InvalidArgumentException(
                "Tipo de alternativa inválido! Tipos aceitos: " . implode(', ', $tiposValidos)
            );
        }

        $this->tipoAlternativa = $tipoAlternativa;
        return $this;
    }

    public function getIdQuestao(): ?int
    {
        return $this->idQuestao;
    }

    public function setIdQuestao(int $idQuestao): self
    {
        $this->idQuestao = $idQuestao;
        return $this;
    }
}
