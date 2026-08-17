<?php

declare(strict_types=1);

class ProgressoMissao
{
    protected Usuario $usuario;
    protected Missao $missao;
    protected int $progresso;

    public function __construct(Usuario $usuario, Missao $missao, int $progresso)
    {
        $this->setUsuario($usuario);
        $this->setMissao($missao);
        $this->setProgresso($progresso);
    }

    public function getUsuario(): Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(Usuario $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getMissao(): Missao
    {
        return $this->missao;
    }

    public function setMissao(Missao $missao): self
    {
        $this->missao = $missao;

        return $this;
    }

    public function getProgresso(): int
    {
        return $this->progresso;
    }

    public function setProgresso(int $progresso): self
    {

        if (!in_array($progresso, [0, 100])) {
            throw new DomainException("Progresso inválido!");
        }

        $this->progresso = $progresso;

        return $this;
    }
}
