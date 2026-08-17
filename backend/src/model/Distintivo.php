<?php

declare(strict_types=1);

class Distintivo
{
    private ?int $idDistintivo = null;
    private string $titulo;
    private float $pontuacao;
    private string $nomeArquivo;

    public function __construct(
        ?int $idDistintivo,
        string $titulo,
        float $pontuacao,
        string $nomeArquivo
    ) {
        $this->setIdDistintivo($idDistintivo);
        $this->setTitulo($titulo);
        $this->setPontuacao($pontuacao);
        $this->setNomeArquivo($nomeArquivo);
    }

    public function getIdDistintivo(): ?int
    {
        return $this->idDistintivo;
    }

    public function setIdDistintivo(?int $idDistintivo): self
    {
        if ($this->idDistintivo !== null && $idDistintivo !== $this->idDistintivo) {
            throw new Exception("ID já definido!");
        }

        $this->idDistintivo = $idDistintivo;
        return $this;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $titulo = trim($titulo);

        if ($titulo === "") {
            throw new DomainException("O distintivo precisa de um nome!");
        }

        $this->titulo = $titulo;
        return $this;
    }

    public function getPontuacao(): float
    {
        return $this->pontuacao;
    }

    public function setPontuacao(float $pontuacao): self
    {
        if ($pontuacao <= 0) {
            throw new DomainException("O distintivo não pode ter a pontuação menor ou igual a zero!");
        }

        $this->pontuacao = $pontuacao;
        return $this;
    }

    public function getNomeArquivo(): string
    {
        return $this->nomeArquivo;
    }

    public function setNomeArquivo(string $nomeArquivo): self
    {
        $nomeArquivo = trim($nomeArquivo);

        if ($nomeArquivo === "") {
            $this->nomeArquivo = "badgeDefault.svg";
            return $this;
        }

        $this->nomeArquivo = $nomeArquivo;
        return $this;
    }
}
