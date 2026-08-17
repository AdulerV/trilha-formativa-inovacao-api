<?php

declare(strict_types=1);

class AlternativaAssociacao extends Alternativa
{
    private ?Alternativa $alternativaAssociada = null;

    public function __construct(
        ?int $idAlternativa,
        string $texto,
        ?Alternativa $alternativaAssociada = null
    ) {
        parent::__construct(
            $idAlternativa,
            $texto,
            self::TIPO_ASSOCIACAO
        );

        if ($alternativaAssociada !== null) {
            $this->setAlternativaAssociada($alternativaAssociada);
        }
    }

    public function getAlternativaAssociada(): ?Alternativa
    {
        return $this->alternativaAssociada;
    }

    public function setAlternativaAssociada(Alternativa $alternativaAssociada): self
    {
        if (
            $this->getIdAlternativa() !== null &&
            $alternativaAssociada->getIdAlternativa() !== null &&
            $alternativaAssociada->getIdAlternativa() === $this->getIdAlternativa()
        ) {
            throw new DomainException("Uma alternativa não pode ser associada a ela mesma!");
        }

        $this->alternativaAssociada = $alternativaAssociada;
        return $this;
    }

    public function getIdAlternativaAssociada(): ?int
    {
        return $this->alternativaAssociada !== null
            ? $this->alternativaAssociada->getIdAlternativa()
            : null;
    }
}
