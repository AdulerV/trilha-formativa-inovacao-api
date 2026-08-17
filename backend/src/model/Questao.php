<?php

declare(strict_types=1);

class Questao
{
    private ?int $idQuestao = null;
    private string $enunciado;
    private string $mensagemCorrecao;
    private array $alternativas;
    private int $idMissao;

    public function __construct(
        ?int $idQuestao,
        string $enunciado,
        string $mensagemCorrecao,
        int $idMissao
    ) {
        $this->setIdQuestao($idQuestao);
        $this->setEnunciado($enunciado);
        $this->setMensagemCorrecao($mensagemCorrecao);
        $this->alternativas = [];
        $this->setIdMissao($idMissao);
    }

    public function getIdQuestao(): ?int
    {
        return $this->idQuestao;
    }

    public function setIdQuestao(?int $idQuestao): self
    {
        if ($this->idQuestao !== null && $idQuestao !== $this->idQuestao) {
            throw new Exception("ID já definido!");
        }

        $this->idQuestao = $idQuestao;
        return $this;
    }

    public function getEnunciado(): string
    {
        return $this->enunciado;
    }

    public function setEnunciado(string $enunciado): self
    {
        $enunciado = trim($enunciado);

        if ($enunciado === "") {
            throw new DomainException("A questão precisa conter um enunciado!");
        }

        $this->enunciado = $enunciado;
        return $this;
    }

    public function getMensagemCorrecao(): string
    {
        return $this->mensagemCorrecao;
    }

    public function setMensagemCorrecao(string $mensagemCorrecao): self
    {
        $mensagemCorrecao = trim($mensagemCorrecao);

        if ($mensagemCorrecao === "") {
            throw new DomainException("A questão precisa de uma mensagem de correção!");
        }

        $this->mensagemCorrecao = $mensagemCorrecao;
        return $this;
    }

    public function getAlternativas(): array
    {
        return $this->alternativas;
    }

    public function adicionarAlternativa(
        string $texto,
        string $tipoAlternativa,
        ?int $id = null,
        array $dadosAdicionais = []
    ): void {
        $this->alternativas[] = match ($tipoAlternativa) {
            Alternativa::TIPO_ORDENACAO => new AlternativaOrdenacao(
                $id,
                $texto,
                $dadosAdicionais['numeroSequencia']
                    ?? throw new DomainException(
                        "Faltando o Número na Sequência para alternativa de Ordenação."
                    )
            ),

            Alternativa::TIPO_ASSOCIACAO => (function () use (
                $id,
                $texto,
                $dadosAdicionais
            ) {
                $associada = $dadosAdicionais['alternativaAssociada']
                    ?? throw new DomainException(
                        "Faltando a Alternativa Associada para Alternativa de Associação."
                    );

                if (is_array($associada)) {
                    $associada = new class(
                        $associada['idAlternativaAssociada'] ?? null,
                        $associada['texto'],
                        Alternativa::TIPO_ASSOCIACAO
                    ) extends Alternativa {};
                }

                return new AlternativaAssociacao(
                    $id,
                    $texto,
                    $associada
                );
            })(),

            Alternativa::TIPO_MULTIPLA_ESCOLHA => new AlternativaMultiplaEscolha(
                $id,
                $texto,
                $dadosAdicionais['correta'] ?? throw new DomainException("Faltando o campo 'correta' para Múltipla Escolha."),
                $dadosAdicionais['subtipo'] ?? throw new DomainException("Faltando o Subtipo para alternativa de Múltipla Escolha.")
            ),

            default => throw new DomainException("Tipo de alternativa desconhecido: {$tipoAlternativa}")
        };
    }

    public function removerAlternativa(int $indice): void
    {
        if (!isset($this->alternativas[$indice])) {
            throw new DomainException("Alternativa não encontrada!");
        }

        unset($this->alternativas[$indice]);
        $this->alternativas = array_values($this->alternativas);
    }

    public function getIdMissao(): int
    {
        return $this->idMissao;
    }

    public function setIdMissao(int $idMissao): self
    {
        $this->idMissao = $idMissao;
        return $this;
    }
}