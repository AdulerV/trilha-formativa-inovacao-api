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

    /**
     * Devolve a alternativa criada para que o chamador possa informar
     * o ID gerado na resposta da API — sem isso o frontend precisava
     * relistar tudo para descobrir o que acabou de salvar.
     */
    public function adicionarAlternativa(
        string $texto,
        string $tipoAlternativa,
        ?int $id = null,
        array $dadosAdicionais = []
    ): Alternativa {
        $this->alternativas[] = $alternativa = match ($tipoAlternativa) {
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
                    /*
                     * A leitura devolve a associada com a chave "id"
                     * (AlternativaDTO::toArray) e a escrita esperava
                     * "idAlternativaAssociada". Quem reenviava para
                     * edição o objeto recebido da API perdia o ID no
                     * caminho, e a associação era recriada em vez de
                     * atualizada. Aceitar as duas grafias resolve a
                     * assimetria sem mudar o contrato de resposta.
                     */
                    $idAssociada = $associada['idAlternativaAssociada']
                        ?? $associada['id']
                        ?? null;

                    if (!isset($associada['texto'])) {
                        throw new DomainException(
                            "A alternativa associada precisa conter um texto."
                        );
                    }

                    $associada = new class(
                        $idAssociada !== null ? (int) $idAssociada : null,
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

        return $alternativa;
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