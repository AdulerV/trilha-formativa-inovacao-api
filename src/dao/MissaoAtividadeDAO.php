<?php

declare(strict_types=1);

class MissaoAtividadeDAO
{
    private PDO $conexao;
    private MissaoAtividadeTarefaDAO $tarefaDAO;
    private QuestaoDAO $questaoDAO;

    public function __construct(
        PDO $conexao,
        MissaoAtividadeTarefaDAO $tarefaDAO,
        QuestaoDAO $questaoDAO
    ) {
        $this->conexao = $conexao;
        $this->tarefaDAO = $tarefaDAO;
        $this->questaoDAO = $questaoDAO;
    }

    public function salvar(int $idMissao, MissaoAtividade $missao): void
    {
        try {
            $this->inserirMissaoBase($idMissao, $missao);

            foreach ($missao->getQuestoes() as $questao) {
                $this->questaoDAO->salvar(
                    $idMissao,
                    $questao
                );
            }

            $this->salvarEspecializacao($idMissao, $missao);
        } catch (PDOException $e) {
            throw new Exception("Erro ao salvar missão do tipo atividade: " . $e->getMessage(), 0, $e);
        }
    }

    private function inserirMissaoBase(int $idMissao, MissaoAtividade $missao): void
    {
        $sql = "INSERT INTO missao_atividade (IdMissao, TipoAtividade)
                VALUES (:idMissao, :tipoAtividade)";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":idMissao", $idMissao);
        $stmt->bindValue(":tipoAtividade", $missao->getTipoAtividade());
        $stmt->execute();
    }

    private function salvarEspecializacao(int $idMissao, MissaoAtividade $missao): void
    {
        if ($missao instanceof MissaoAtividadeTarefa) {
            $this->tarefaDAO->salvar($idMissao, $missao);
        }
    }

    public function listar(): array
    {
        try {
            $sql = "SELECT 
                        m.IdMissao,
                        m.Titulo,
                        m.Pontuacao,
                        ma.TipoAtividade,
                        t.IdTematica,
                        t.Titulo AS TituloTematica,
                        d.IdDistintivo,
                        d.Titulo AS TituloDistintivo,
                        d.Pontuacao AS PontuacaoDistintivo,
                        d.NomeArquivo AS NomeArquivoDistintivo
                    FROM missao AS m
                    INNER JOIN missao_atividade AS ma ON m.IdMissao = ma.IdMissao
                    LEFT JOIN missao_atividade_tarefa AS mat ON ma.IdMissao = mat.IdMissao
                    INNER JOIN tematica AS t ON t.IdTematica = m.IdTematica
                    LEFT JOIN distintivo AS d ON d.IdDistintivo = mat.IdDistintivo";

            $stmt = $this->conexao->prepare($sql);
            $stmt->execute();

            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $missoes = [];

            foreach ($registros as $registro) {
                $missao = $this->mapearMissaoAtividade($registro);
                $this->adicionarQuestoesMissao($missao);
                $missoes[] = $missao;
            }

            return $missoes;
        } catch (PDOException $e) {
            throw new Exception("Erro ao listar missões do tipo atividade: " . $e->getMessage(), 0, $e);
        }
    }

    public function buscarPorId(int $idMissao): ?MissaoAtividade
    {
        try {
            $sql = "SELECT TipoAtividade 
                    FROM missao_atividade 
                    WHERE IdMissao = :idMissao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idMissao", $idMissao);
            $stmt->execute();

            $tipo = $stmt->fetchColumn();

            if ($tipo === false) {
                return null;
            }

            return match (strtolower((string) $tipo)) {
                MissaoAtividade::QUIZ => $this->buscarQuiz($idMissao),
                MissaoAtividade::TAREFA, MissaoAtividade::TAREFA_FINAL => $this->tarefaDAO->buscarPorId($idMissao),
                default => throw new Exception("Tipo de missão de atividade inválido: '{$tipo}'")
            };
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar missão de atividade: " . $e->getMessage(), 0, $e);
        }
    }

    private function buscarQuiz(int $idMissao): ?MissaoAtividade
    {
        $sql = "SELECT
                    m.IdMissao,
                    m.Titulo,
                    m.Pontuacao,
                    ma.TipoAtividade,
                    t.IdTematica,
                    t.Titulo AS TituloTematica
                FROM missao AS m
                INNER JOIN missao_atividade AS ma ON m.IdMissao = ma.IdMissao
                INNER JOIN tematica AS t ON t.IdTematica = m.IdTematica
                WHERE ma.IdMissao = :idMissao";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":idMissao", $idMissao);
        $stmt->execute();

        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$registro) {
            return null;
        }

        $missao = $this->mapearMissaoAtividade($registro);
        $this->adicionarQuestoesMissao($missao);

        return $missao;
    }

    public function atualizar(MissaoAtividade $missao): void
    {
        try {
            $sql = "UPDATE missao_atividade 
                    SET TipoAtividade = :tipoAtividade
                    WHERE IdMissao = :idMissao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":tipoAtividade", $missao->getTipoAtividade());
            $stmt->bindValue(":idMissao", $missao->getIdMissao());
            $stmt->execute();

            if ($missao instanceof MissaoAtividadeTarefa) {
                $this->tarefaDAO->atualizar(
                    $missao->getIdMissao(),
                    $missao
                );
            }
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar missão do tipo atividade: " . $e->getMessage(), 0, $e);
        }
    }

    private function mapearMissaoAtividade(array $dados): MissaoAtividade
    {
        if (
            array_key_exists("IdDistintivo", $dados) &&
            !is_null($dados["IdDistintivo"]) &&
            in_array($dados["TipoAtividade"], ["tarefa", "tarefa final"])
        ) {
            return $this->tarefaDAO->mapearMissaoAtividadeTarefa($dados);
        }

        $tematica = new Tematica(
            (int) $dados["IdTematica"],
            $dados["TituloTematica"]
        );

        return new MissaoAtividade(
            (int) $dados["IdMissao"],
            $dados["Titulo"],
            (float) $dados["Pontuacao"],
            $tematica,
            $dados["TipoAtividade"] ?? MissaoAtividade::QUIZ
        );
    }

    private function adicionarQuestoesMissao(MissaoAtividade $missao): void
    {
        $questoes = $this->questaoDAO->listarPorMissao(
            $missao->getIdMissao()
        );

        foreach ($questoes as $questao) {
            $questaoMissao = $missao->adicionarQuestao(
                $questao->getEnunciado(),
                $questao->getMensagemCorrecao(),
                $questao->getIdQuestao()
            );

            $this->adicionarAlternativasQuestao(
                $questao,
                $questaoMissao
            );
        }
    }

    private function adicionarAlternativasQuestao(
        Questao $questaoOrigem,
        Questao $questaoDestino
    ): void {
        foreach ($questaoOrigem->getAlternativas() as $alternativa) {
            $dadosAdicionais = [];

            if ($alternativa instanceof AlternativaOrdenacao) {
                $dadosAdicionais['numeroSequencia'] = $alternativa->getNumeroSequencia();
            }

            if ($alternativa instanceof AlternativaMultiplaEscolha) {
                $dadosAdicionais['correta'] = $alternativa->isCorreta();
                $dadosAdicionais['subtipo'] = $alternativa->getTipoMultiplaEscolha();
            }

            if ($alternativa instanceof AlternativaAssociacao) {
                $dadosAdicionais['alternativaAssociada'] = $alternativa->getAlternativaAssociada();
            }

            $questaoDestino->adicionarAlternativa(
                $alternativa->getTexto(),
                $alternativa->getTipoAlternativa(),
                $alternativa->getIdAlternativa(),
                $dadosAdicionais
            );
        }
    }
}