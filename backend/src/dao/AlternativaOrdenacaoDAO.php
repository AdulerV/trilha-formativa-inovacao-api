<?php

declare(strict_types=1);

class AlternativaOrdenacaoDAO
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function salvar(int $idAlternativa, AlternativaOrdenacao $alternativa): void
    {
        try {
            $sql = "INSERT INTO ALTERNATIVA_ORDENACAO (IdAlternativa, NumeroSequencia)
                    VALUES (:idAlternativa, :numeroSequencia)";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idAlternativa", $idAlternativa, PDO::PARAM_INT);
            $stmt->bindValue(":numeroSequencia", $alternativa->getNumeroSequencia(), PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao salvar alternativa do tipo ordenação!");
        }
    }

    public function listar(): array
    {
        try {
            $sql = "SELECT 
                        a.IdAlternativa,
                        a.Texto,
                        a.IdQuestao,
                        ao.NumeroSequencia
                    FROM ALTERNATIVA_ORDENACAO AS ao
                    INNER JOIN alternativa AS a ON a.IdAlternativa = ao.IdAlternativa";

            $stmt = $this->conexao->prepare($sql);
            $stmt->execute();

            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $alternativas = [];

            foreach ($registros as $registro) {
                $alternativas[] = $this->mapearAlternativaOrdenacao($registro);
            }

            return $alternativas;
        } catch (PDOException) {
            throw new Exception("Erro ao listar alternativas do tipo ordenacao!");
        }
    }

    public function listarPorQuestao(int $idQuestao): array
    {
        try {
            $sql = "SELECT 
                        a.IdAlternativa,
                        a.Texto,
                        a.IdQuestao,
                        ao.NumeroSequencia
                    FROM ALTERNATIVA_ORDENACAO AS ao
                    INNER JOIN alternativa AS a ON a.IdAlternativa = ao.IdAlternativa
                    WHERE a.IdQuestao = :idQuestao
                    ORDER BY ao.NumeroSequencia ASC";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idQuestao", $idQuestao, PDO::PARAM_INT);
            $stmt->execute();

            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $alternativas = [];

            foreach ($registros as $registro) {
                $alternativas[] = $this->mapearAlternativaOrdenacao($registro);
            }

            return $alternativas;
        } catch (PDOException) {
            throw new Exception("Erro ao listar alternativas de ordenacao para a questao especificada!");
        }
    }

    public function buscarPorId(int $idAlternativa): AlternativaOrdenacao
    {
        try {
            $sql = "SELECT 
                        a.IdAlternativa,
                        a.Texto,
                        a.IdQuestao,
                        ao.NumeroSequencia
                    FROM ALTERNATIVA_ORDENACAO AS ao
                    INNER JOIN alternativa AS a ON a.IdAlternativa = ao.IdAlternativa
                    WHERE ao.IdAlternativa = :idAlternativa";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idAlternativa", $idAlternativa, PDO::PARAM_INT);
            $stmt->execute();

            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$registro) {
                throw new Exception("Alternativa de ordenacao nao encontrada!");
            }

            return $this->mapearAlternativaOrdenacao($registro);
        } catch (PDOException) {
            throw new Exception("Erro ao encontrar alternativa do tipo ordenacao especificada!");
        }
    }

    public function atualizar(AlternativaOrdenacao $alternativa): void
    {
        try {
            $sql = "SELECT NumeroSequencia
            FROM ALTERNATIVA_ORDENACAO
            WHERE IdAlternativa = :idAlternativa";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idAlternativa", $alternativa->getIdAlternativa(), PDO::PARAM_INT);
            $stmt->execute();

            $numeroSequenciaAtual = (int) $stmt->fetchColumn();

            $sql = "UPDATE ALTERNATIVA_ORDENACAO ao
            INNER JOIN ALTERNATIVA a1
                ON ao.IdAlternativa = a1.IdAlternativa
            INNER JOIN ALTERNATIVA a2
                ON a1.IdQuestao = a2.IdQuestao
            SET ao.NumeroSequencia = :numeroSequenciaAtual
            WHERE a2.IdAlternativa = :idAlternativa
                AND ao.NumeroSequencia = :numeroSequenciaNova";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":numeroSequenciaAtual", $numeroSequenciaAtual, PDO::PARAM_INT);
            $stmt->bindValue(":numeroSequenciaNova", $alternativa->getNumeroSequencia(), PDO::PARAM_INT);
            $stmt->bindValue(":idAlternativa", $alternativa->getIdAlternativa(), PDO::PARAM_INT);
            $stmt->execute();

            $sql = "UPDATE ALTERNATIVA_ORDENACAO
            SET NumeroSequencia = :numeroSequencia
            WHERE IdAlternativa = :idAlternativa";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":numeroSequencia", $alternativa->getNumeroSequencia(), PDO::PARAM_INT);
            $stmt->bindValue(":idAlternativa", $alternativa->getIdAlternativa(), PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao atualizar alternativa do tipo ordenacao!");
        }
    }

    private function mapearAlternativaOrdenacao(array $dados): AlternativaOrdenacao
    {
        $alternativa = new AlternativaOrdenacao(
            (int) $dados["IdAlternativa"],
            $dados["Texto"],
            (int) $dados["NumeroSequencia"]
        );

        if (isset($dados["IdQuestao"]) && $dados["IdQuestao"] !== null) {
            $alternativa->setIdQuestao((int) $dados["IdQuestao"]);
        }

        return $alternativa;
    }
}