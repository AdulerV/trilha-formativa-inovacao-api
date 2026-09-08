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

    /**
     * Reconstrói um progresso já persistido sem reaplicar as regras de
     * criação.
     *
     * setProgresso() só aceita 0 ou 100 — regra correta na escrita.
     * Na leitura, um único registro com valor intermediário (ou com
     * mais tentativas que o limite atual) fazia a listagem completa
     * responder 500, derrubando o ranking de todos os usuários.
     */
    public static function rehidratar(
        Usuario $usuario,
        Missao $missao,
        int $progresso
    ): static {
        $instancia = (new ReflectionClass(static::class))->newInstanceWithoutConstructor();

        $instancia->usuario = $usuario;
        $instancia->missao = $missao;
        $instancia->progresso = $progresso;

        return $instancia;
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
