<?php

class DistintivoAdquirido
{
    private Usuario $usuario;
    private Distintivo $distintivo;

    public function __construct(
        Usuario $usuario,
        Distintivo $distintivo
    ) {
        $this->setUsuario($usuario);
        $this->setDistintivo($distintivo);
    }

    public function getUsuario(): Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(
        Usuario $usuario
    ): self {
        if (
            isset($this->usuario) &&
            $this->usuario->getIdUsuario()
            !== $usuario->getIdUsuario()
        ) {
            throw new DomainException(
                "Usuário já definido!"
            );
        }

        $this->usuario = $usuario;

        return $this;
    }

    public function getDistintivo(): Distintivo
    {
        return $this->distintivo;
    }

    public function setDistintivo(
        Distintivo $distintivo
    ): self {
        if (
            isset($this->distintivo) &&
            $this->distintivo->getIdDistintivo()
            !== $distintivo->getIdDistintivo()
        ) {
            throw new DomainException(
                "Distintivo já definido!"
            );
        }

        $this->distintivo = $distintivo;

        return $this;
    }
}
