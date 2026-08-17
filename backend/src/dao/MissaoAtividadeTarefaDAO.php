<?php

declare(strict_types=1);

class MissaoAtividadeTarefaDAO
{
    private PDO $conexao;
    private QuestaoDAO $questaoDAO;

    public function __construct(
        PDO $conexao,
        QuestaoDAO $questaoDAO
    ) {
        $this->conexao = $conexao;
        $this->questaoDAO = $questaoDAO;
    }

    public function salvar(int $idMissao, MissaoAtividadeTarefa $missao): void
    {
        try {
            $sql = "INSERT INTO missao_atividade_tarefa (IdMissao, IdDistintivo)
                    VALUES (:idMissao, :idDistintivo)";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idMissao", $idMissao);
            $stmt->bindValue(":idDistintivo", $missao->getDistintivo()->getIdDistintivo());
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao salvar missão do tipo atividade tarefa!");
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
                    INNER JOIN tematica AS t ON t.IdTematica = m.IdTematica
                    INNER JOIN missao_atividade_tarefa AS mat ON ma.IdMissao = mat.IdMissao
                    INNER JOIN distintivo AS d ON mat.IdDistintivo = d.IdDistintivo";

            $stmt = $this->conexao->prepare($sql);
            $stmt->execute();

            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $missoes = [];

            foreach ($registros as $registro) {
                $missoes[] = $this->mapearMissaoAtividadeTarefa($registro);
            }

            return $missoes;
        } catch (PDOException) {
            throw new Exception("Erro ao listar missões do tipo atividade tarefa!");
        }
    }

    public function buscarPorId(int $idMissao): ?MissaoAtividadeTarefa
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
                    INNER JOIN tematica AS t ON t.IdTematica = m.IdTematica
                    INNER JOIN missao_atividade_tarefa AS mat ON ma.IdMissao = mat.IdMissao
                    INNER JOIN distintivo AS d ON mat.IdDistintivo = d.IdDistintivo
                    WHERE mat.IdMissao = :idMissao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idMissao", $idMissao);
            $stmt->execute();

            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$registro) {
                return null;
            }
            $missao = $this->mapearMissaoAtividadeTarefa($registro);

            $questoes = $this->questaoDAO->listarPorMissao($idMissao);

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
            return $missao;
        } catch (PDOException) {
            throw new Exception("Erro ao buscar missão do tipo atividade tarefa!");
        }
    }

    public function atualizar(int $idMissao, MissaoAtividadeTarefa $missao): void
    {
        try {
            $sql = "UPDATE missao_atividade_tarefa 
                        SET IdDistintivo = :idDistintivo
                        WHERE IdMissao = :idMissao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idMissao", $idMissao);
            $stmt->bindValue(":idDistintivo", $missao->getDistintivo()->getIdDistintivo());
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao atualizar missão do tipo atividade tarefa!");
        }
    }

    public function mapearMissaoAtividadeTarefa(array $dados): MissaoAtividadeTarefa
    {
        $tematica = new Tematica(
            (int) $dados["IdTematica"],
            $dados["TituloTematica"]
        );

        $distintivo = new Distintivo(
            (int) $dados["IdDistintivo"],
            $dados["TituloDistintivo"],
            (float) $dados["PontuacaoDistintivo"],
            $dados["NomeArquivoDistintivo"]
        );

        return new MissaoAtividadeTarefa(
            (int) $dados["IdMissao"],
            $dados["Titulo"],
            (float) $dados["Pontuacao"],
            $tematica,
            $dados["TipoAtividade"],
            $distintivo
        );
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
