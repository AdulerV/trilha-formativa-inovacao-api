<?php

declare(strict_types=1);

class AlternativaMarcada
{
    private Usuario $usuario;
    private Alternativa $alternativa;
    private bool $correta;
    private ?int $sequenciaRespondida;
    private ?int $idAlternativaAssociadaRespondida;

    public function __construct(
        Usuario $usuario,
        Alternativa $alternativa,
        bool $correta = false,
        ?int $sequenciaRespondida = null,
        ?int $idAlternativaAssociadaRespondida = null
    ) {
        $this->usuario = $usuario;
        $this->alternativa = $alternativa;
        $this->correta = $correta;
        $this->sequenciaRespondida = $sequenciaRespondida;
        $this->idAlternativaAssociadaRespondida = $idAlternativaAssociadaRespondida;
    }

    public function getUsuario(): Usuario
    {
        return $this->usuario;
    }

    public function getAlternativa(): Alternativa
    {
        return $this->alternativa;
    }

    public function isCorreta(): bool
    {
        return $this->correta;
    }

    public function setCorreta(bool $correta): void
    {
        $this->correta = $correta;
    }

    public function getSequenciaRespondida(): ?int
    {
        return $this->sequenciaRespondida;
    }

    public function getIdAlternativaAssociadaRespondida(): ?int
    {
        return $this->idAlternativaAssociadaRespondida;
    }
}
