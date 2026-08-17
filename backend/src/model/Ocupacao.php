<?php

declare(strict_types=1);
class Ocupacao
{
    private ?int $idOcupacao = null;
    private string $titulo;

    public function __construct(?int $idOcupacao, string $titulo)
    {
        $this->setIdOcupacao($idOcupacao);
        $this->setTitulo($titulo);
    }

    public function getIdOcupacao(): ?int
    {
        return $this->idOcupacao;
    }

    public function setIdOcupacao(?int $idOcupacao): self
    {
        if ($this->idOcupacao !== null && $idOcupacao !== $this->idOcupacao) {
            throw new Exception("ID já definido!");
        }

        $this->idOcupacao = $idOcupacao;
        return $this;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $titulo = strtolower(trim($titulo));

        if (empty($titulo)) {
            throw new DomainException("Ocupação inválida!");
        }

        $this->titulo = $titulo;

        return $this;
    }
}
