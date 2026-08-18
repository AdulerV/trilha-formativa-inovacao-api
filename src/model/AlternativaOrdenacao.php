<?php

declare(strict_types=1);

class AlternativaOrdenacao extends Alternativa
{
    private int $numeroSequencia;

    public function __construct(
        ?int $idAlternativa,
        string $texto,
        int $numeroSequencia
    ) {
        parent::__construct(
            $idAlternativa,
            $texto,
            self::TIPO_ORDENACAO
        );

        $this->setNumeroSequencia($numeroSequencia);
    }

    public function getNumeroSequencia(): int
    {
        return $this->numeroSequencia;
    }

    public function setNumeroSequencia(int $numeroSequencia): self
    {
        if ($numeroSequencia <= 0) {
            throw new DomainException("O numero na sequencia deve ser maior que zero!");
        }

        $this->numeroSequencia = $numeroSequencia;
        return $this;
    }
}
