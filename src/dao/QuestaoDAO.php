<?php

declare(strict_types=1);

class QuestaoDAO
{
    private PDO $conexao;
    private AlternativaDAO $alternativaDAO;

    public function __construct(PDO $conexao, AlternativaDAO $alternativaDAO)
    {
        $this->conexao = $conexao;
        $this->alternativaDAO = $alternativaDAO;
    }

    public function salvar(int $idMissao, Questao $questao): void
    {
        try {
            $sql = "INSERT INTO questao 
                    (Enunciado, MensagemCorrecao, IdMissao) 
                    VALUES (:enunciado, :mensagem, :idMissao)";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":enunciado", $questao->getEnunciado());
            $stmt->bindValue(":mensagem", $questao->getMensagemCorrecao());
            $stmt->bindValue(":idMissao", $idMissao);
            $stmt->execute();

            $idQuestao = (int) $this->conexao->lastInsertId();
            $questao->setIdQuestao($idQuestao);
        } catch (PDOException $e) {
            throw new Exception("Erro ao salvar questão: " . $e->getMessage());
        }
    }

    public function listar(): array
    {
        try {
            $sql = "SELECT * FROM questao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->execute();

            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $questoes = [];

            foreach ($registros as $registro) {
                $questao = $this->mapearQuestao($registro);
                $this->adicionarAlternativasNaQuestao($questao);
                $questoes[] = $questao;
            }

            return $questoes;
        } catch (PDOException $e) {
            throw new Exception("Erro ao listar questoes: " . $e->getMessage());
        }
    }

    public function listarPorMissao(int $idMissao): array
    {
        try {
            $sql = "SELECT * FROM questao WHERE IdMissao = :idMissao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idMissao", $idMissao, PDO::PARAM_INT);
            $stmt->execute();

            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $questoes = [];

            foreach ($registros as $registro) {
                $questao = $this->mapearQuestao($registro);
                $this->adicionarAlternativasNaQuestao($questao);
                $questoes[] = $questao;
            }

            return $questoes;
        } catch (PDOException $e) {
            throw new Exception("Erro ao listar questoes por missao: " . $e->getMessage());
        }
    }

    public function buscarPorId(int $idQuestao): Questao
    {
        try {
            $sql = "SELECT * FROM questao WHERE IdQuestao = :idQuestao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idQuestao", $idQuestao);
            $stmt->execute();

            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$registro) {
                throw new Exception("Questão não encontrada!");
            }

            $questao = $this->mapearQuestao($registro);
            $this->adicionarAlternativasNaQuestao($questao);

            return $questao;
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar questão: " . $e->getMessage());
        }
    }

    public function buscarIdMissao(int $idQuestao): int
    {
        try {
            $sql = "SELECT IdMissao FROM questao WHERE IdQuestao = :idQuestao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idQuestao", $idQuestao);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar id da missão: " . $e->getMessage());
        }
    }

    public function atualizar(Questao $questao): void
    {
        try {
            $sql = "UPDATE questao SET 
                    Enunciado = :enunciado,
                    MensagemCorrecao = :mensagemCorrecao
                WHERE IdQuestao = :idQuestao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":enunciado", $questao->getEnunciado());
            $stmt->bindValue(":mensagemCorrecao", $questao->getMensagemCorrecao());
            $stmt->bindValue(":idQuestao", $questao->getIdQuestao());
            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar questão: " . $e->getMessage());
        }
    }

    public function deletar(int $idQuestao): void
    {
        try {
            $sql = "DELETE FROM questao WHERE IdQuestao = :idQuestao";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idQuestao", $idQuestao);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao deletar questão: " . $e->getMessage());
        }
    }

    private function mapearQuestao(array $dados): Questao
    {
        return new Questao(
            (int) $dados["IdQuestao"],
            $dados["Enunciado"],
            $dados["MensagemCorrecao"],
            (int) $dados["IdMissao"]
        );
    }

    private function adicionarAlternativasNaQuestao(Questao $questao): void
    {
        $alternativas = $this->alternativaDAO->listarPorQuestao($questao->getIdQuestao());

        foreach ($alternativas as $alternativa) {
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

            $questao->adicionarAlternativa(
                $alternativa->getTexto(),
                $alternativa->getTipoAlternativa(),
                $alternativa->getIdAlternativa(),
                $dadosAdicionais
            );
        }
    }

    public function verificarSeQuestaoExistePorId(int $idQuestao): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM questao WHERE IdQuestao = :idQuestao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':idQuestao', $idQuestao);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar se questão existe por ID: " . $e->getMessage());
        }
    }

    public function verificarSeEnunciadoExiste(int $idMissao, string $enunciado): bool 
    {
        try {
            $sql = "SELECT COUNT(*) FROM questao 
                WHERE Enunciado = :enunciado 
                AND IdMissao = :idMissao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':enunciado', $enunciado);
            $stmt->bindValue(':idMissao', $idMissao);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar se enunciado existe: " . $e->getMessage());
        }
    }

    public function verificarEnunciadoParaOutraQuestao(string $enunciado, int $idQuestao): bool 
    {
        try {
            $sql = "SELECT COUNT(*)
            FROM questao q
            WHERE q.Enunciado = :enunciado
              AND q.IdQuestao != :idQuestao
              AND q.IdMissao = (
                    SELECT IdMissao
                    FROM questao
                    WHERE IdQuestao = :idQuestao
              )";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':enunciado', $enunciado);
            $stmt->bindValue(':idQuestao', $idQuestao);
            $stmt->execute();

            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar enunciado para outra questão: " . $e->getMessage());
        }
    }

    public function verificarSeQuestaoExiste(string $enunciado, int $idMissao): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM questao 
                    WHERE enunciado = :enunciado AND idMissao = :idMissao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':enunciado', $enunciado);
            $stmt->bindValue(':idMissao', $idMissao);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar se questão existe: " . $e->getMessage());
        }
    }

    public function verificarSeMensagemExiste(string $mensagem): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM questao WHERE mensagemCorrecao = :msg";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':msg', $mensagem);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar se mensagem existe: " . $e->getMessage());
        }
    }

    public function verificarSeQuestaoExisteParaOutraMissao(int $idQuestao, int $idMissao): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM questao WHERE IdQuestao = :idQuestao AND IdMissao != :idMissao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':idQuestao', $idQuestao);
            $stmt->bindValue(':idMissao', $idMissao);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar se questão existe para outra missão: " . $e->getMessage());
        }
    }
}