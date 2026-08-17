<?php

declare(strict_types=1);

abstract class Missao
{
    protected ?int $idMissao = null;
    protected string $titulo;
    protected float $pontuacao;
    protected string $tipoMissao;
    protected Tematica $tematica;

    public const TIPO_ATIVIDADE = 'atividade';
    public const TIPO_CONTEUDO = 'conteudo';

    public function __construct(
        ?int $idMissao,
        string $titulo,
        float $pontuacao,
        string $tipoMissao,
        Tematica $tematica
    ) {
        $this->setIdMissao($idMissao);
        $this->setTitulo($titulo);
        $this->setPontuacao($pontuacao);
        $this->setTipoMissao($tipoMissao);
        $this->setTematica($tematica);
    }

    public function getIdMissao(): ?int
    {
        return $this->idMissao;
    }

    public function setIdMissao(?int $idMissao): self
    {
        if ($this->idMissao !== null && $idMissao !== $this->idMissao) {
            throw new Exception("ID já definido!");
        }

        $this->idMissao = $idMissao;
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
            throw new DomainException("A missão precisa conter um título!");
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
            throw new DomainException("A missão não pode ter pontuação menor do que zero!");
        }

        $this->pontuacao = $pontuacao;
        return $this;
    }

    public function getTipoMissao(): string
    {
        return $this->tipoMissao;
    }

    public function setTipoMissao(string $tipoMissao): self
    {
        $tipoMissao = trim($tipoMissao);


        $this->tipoMissao = $tipoMissao;
        return $this;
    }

    public function getTematica(): Tematica
    {
        return $this->tematica;
    }

    public function setTematica(Tematica $tematica): self
    {
        $this->tematica = $tematica;
        return $this;
    }

    public function getIdTematica()
    {
        $this->tematica->getIdTematica();
    }
}
