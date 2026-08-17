<?php

declare(strict_types=1);

class MissaoAtividade extends Missao
{
    protected string $tipoAtividade;
    protected array $questoes;

    public const QUIZ = 'quiz';
    public const TAREFA = 'tarefa';
    public const TAREFA_FINAL = 'tarefa final';

    public function __construct(
        ?int $idMissao,
        string $titulo,
        float $pontuacao,
        Tematica $tematica,
        string $tipoAtividade
    ) {
        parent::__construct(
            $idMissao,
            $titulo,
            $pontuacao,
            self::TIPO_ATIVIDADE,
            $tematica
        );

        $this->setTipoAtividade($tipoAtividade);
        $this->questoes = [];
    }

    public function getTipoAtividade(): string
    {
        return $this->tipoAtividade;
    }

    public function setTipoAtividade(string $tipoAtividade): self
    {
        $tipoAtividade = strtolower(trim($tipoAtividade));

        if ($tipoAtividade === "") {
            throw new DomainException("Tipo de atividade inválido!");
        }

        if (!in_array($tipoAtividade, [
            self::QUIZ,
            self::TAREFA_FINAL,
            self::TAREFA
        ])) {
            throw new DomainException("Tipo de atividade inválido!");
        }

        $this->tipoAtividade = $tipoAtividade;

        return $this;
    }

    public function getQuestoes(): array
    {
        return $this->questoes;
    }

    public function adicionarQuestao(
        string $enunciado,
        string $mensagemCorrecao,
        ?int $id = null
    ): Questao {
        $this->questoes[] = $questao = new Questao(
            $id,
            $enunciado,
            $mensagemCorrecao,
            $this->getIdMissao()
        );

        return $questao;
    }

    public function removerQuestao(int $indice)
    {
        if (!isset($this->questoes[$indice])) {
            throw new DomainException("Questão não encontrada!");
        }

        unset($this->questoes[$indice]);
        $this->questoes = array_values($this->questoes);
    }
}
