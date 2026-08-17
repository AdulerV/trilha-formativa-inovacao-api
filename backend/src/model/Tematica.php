<?php

declare(strict_types=1);

class Tematica
{
    private ?int $idTematica = null;
    private string $titulo;

    public function __construct(?int $idTematica, string $titulo)
    {
        $this->setIdTematica($idTematica);
        $this->setTitulo($titulo);
    }

    public function getIdTematica(): ?int
    {
        return $this->idTematica;
    }

    public function setIdTematica(?int $idTematica): self
    {
        if ($this->idTematica !== null && $idTematica !== $this->idTematica) {
            throw new Exception("ID já definido!");
        }

        $this->idTematica = $idTematica;

        return $this;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $titulo = trim($titulo);

        if (empty($titulo)) {
            throw new DomainException("Título inválido!");
        }

        $this->titulo = strtolower($titulo);

        return $this;
    }
}
