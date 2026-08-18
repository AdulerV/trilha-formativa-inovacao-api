<?php

declare(strict_types=1);

final class MissaoAtividadeTarefa extends MissaoAtividade
{
    protected Distintivo $distintivo;

    public function __construct(
        ?int $idMissao,
        string $titulo,
        float $pontuacao,
        Tematica $tematica,
        string $tipoAtividade,
        Distintivo $distintivo
    ) {
        $this->verificarTipoAtividade($tipoAtividade);

        parent::__construct(
            $idMissao,
            $titulo,
            $pontuacao,
            $tematica,
            $tipoAtividade
        );

        $this->setDistintivo($distintivo);
    }

    private function verificarTipoAtividade(string $tipoAtividade): void
    {
        if (!in_array($tipoAtividade, [
            self::TAREFA,
            self::TAREFA_FINAL
        ])) {
            throw new DomainException("Tipo de tarefa inválido!");
        }
    }

    public function getDistintivo(): Distintivo
    {
        return $this->distintivo;
    }

    public function setDistintivo(Distintivo $distintivo): self
    {
        $this->distintivo = $distintivo;
        return $this;
    }
}
