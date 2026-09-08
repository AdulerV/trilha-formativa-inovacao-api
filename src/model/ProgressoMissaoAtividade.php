<?php

class ProgressoMissaoAtividade extends ProgressoMissao
{
    private int $tentativasRealizadas;
    private float $pontuacaoObtida;

    private const MAX_TENTATIVAS = 3;

    public function __construct(
        Usuario $usuario,
        Missao $missao,
        int $progresso,
        int $tentativasRealizadas,
        float $pontuacaoObtida
    ) {
        $this->setTentativasRealizadas($tentativasRealizadas);

        parent::__construct($usuario, $missao, $progresso);

        $this->setPontuacaoObtida($pontuacaoObtida);
    }

    /**
     * Reconstrói o progresso de atividade já persistido.
     *
     * Complementa ProgressoMissao::rehidratar() com os campos da
     * especialização, também sem revalidar: tentativas acima do limite
     * atual ou pontuação acima da pontuação vigente da missão são
     * dados históricos legítimos, não motivo para derrubar a listagem.
     */
    public static function rehidratarAtividade(
        Usuario $usuario,
        Missao $missao,
        int $progresso,
        int $tentativasRealizadas,
        float $pontuacaoObtida
    ): self {
        $instancia = self::rehidratar($usuario, $missao, $progresso);

        $instancia->tentativasRealizadas = $tentativasRealizadas;
        $instancia->pontuacaoObtida = $pontuacaoObtida;

        return $instancia;
    }

    #[Override]
    public function setProgresso(int $progresso): ProgressoMissao
    {
        if ($this->tentativasRealizadas === 0 && $progresso !== 0) {
            throw new DomainException("Progresso inválido sem tentativas!");
        }

        if (!in_array($progresso, [0, 100])) {
            throw new DomainException("Progresso inválido!");
        }

        return parent::setProgresso($progresso);
    }

    public function getTentativasRealizadas(): int
    {
        return $this->tentativasRealizadas;
    }

    public function setTentativasRealizadas(int $tentativasRealizadas): self
    {
        if (
            $tentativasRealizadas < 0 ||
            $tentativasRealizadas > self::MAX_TENTATIVAS
        ) {
            throw new DomainException("Quantidade de tentativas inválida!");
        }

        $this->tentativasRealizadas = $tentativasRealizadas;

        return $this;
    }

    public function getPontuacaoObtida(): float
    {
        return $this->pontuacaoObtida;
    }

    public function setPontuacaoObtida(float $pontuacaoObtida): self
    {
        if ($pontuacaoObtida < 0) {
            throw new DomainException("Pontuação inválida!");
        }

        if ($pontuacaoObtida > $this->missao->getPontuacao()) {
            throw new DomainException("Pontuação maior que a permitida!");
        }

        $this->pontuacaoObtida = $pontuacaoObtida;

        return $this;
    }
}