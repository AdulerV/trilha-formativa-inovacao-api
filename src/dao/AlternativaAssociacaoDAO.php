<?php

declare(strict_types=1);

class AlternativaAssociacaoDAO
{
    private PDO $conexao;

    private const SQL_BASE = "SELECT
        a.IdAlternativa,
        a.Texto,
        a.IdQuestao,

        ass.IdAlternativa AS IdAssoc,
        ass.Texto AS TextoAssoc,
        ass.IdQuestao AS IdQuestaoAssoc

    FROM alternativa_associacao aa
    INNER JOIN alternativa a
        ON a.IdAlternativa = aa.IdAlternativa
    INNER JOIN alternativa ass
        ON ass.IdAlternativa = aa.IdAlternativaAssociada";

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function salvar(int $idAlternativa, AlternativaAssociacao $alternativa): void
    {
        try {
            $sql = "INSERT INTO alternativa_associacao (IdAlternativa, IdAlternativaAssociada)
                    VALUES (:idAlternativa, :idAlternativaAssociada)";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idAlternativa", $idAlternativa);
            $stmt->bindValue(":idAlternativaAssociada", $alternativa->getIdAlternativaAssociada());
            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao salvar alternativa do tipo associacao: " . $e->getMessage());
        }
    }

    public function listar(): array
    {
        try {
            $sql = "SELECT 
                        a.IdAlternativa, a.Texto, a.IdQuestao,
                        ass.IdAlternativa AS IdAssoc, 
                        ass.Texto AS TextoAssoc, 
                        ass.IdQuestao AS IdQuestaoAssoc
                    FROM alternativa_associacao AS aa
                    INNER JOIN alternativa AS a ON a.IdAlternativa = aa.IdAlternativa
                    INNER JOIN alternativa AS ass ON ass.IdAlternativa = aa.IdAlternativaAssociada";

            $stmt = $this->conexao->prepare($sql);
            $stmt->execute();

            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $alternativas = [];

            foreach ($registros as $registro) {
                $alternativas[] = $this->mapearAlternativaAssociacao($registro);
            }

            return $alternativas;
        } catch (PDOException $e) {
            throw new Exception("Erro ao listar alternativas do tipo associacao: " . $e->getMessage());
        }
    }

    public function listarPorQuestao(int $idQuestao): array
    {
        try {
            $sql = "SELECT
                a.IdAlternativa,
                a.Texto,
                a.IdQuestao,

                ass.IdAlternativa AS IdAssoc,
                ass.Texto AS TextoAssoc,
                ass.IdQuestao AS IdQuestaoAssoc

            FROM alternativa_associacao aa

            INNER JOIN alternativa a
                ON a.IdAlternativa = aa.IdAlternativa

            INNER JOIN alternativa ass
                ON ass.IdAlternativa = aa.IdAlternativaAssociada

            WHERE a.IdQuestao = :idQuestao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idQuestao", $idQuestao);
            $stmt->execute();

            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $alternativas = [];

            foreach ($registros as $registro) {
                $alternativas[] = $this->mapearAlternativaAssociacao($registro);
            }

            return $alternativas;
        } catch (PDOException $e) {
            throw new Exception("Erro ao listar alternativas do tipo associação por questão: " . $e->getMessage());
        }
    }

    public function buscarPorId(int $idAlternativa): AlternativaAssociacao
    {
        try {
            $sql = self::SQL_BASE . "
            WHERE aa.IdAlternativa = :idAlternativa
            ";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idAlternativa", $idAlternativa);
            $stmt->execute();

            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$registro) {
                throw new Exception("Alternativa de associação não encontrada.");
            }

            return $this->mapearAlternativaAssociacao($registro);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar alternativa do tipo associação: " . $e->getMessage());
        }
    }

    public function atualizar(AlternativaAssociacao $alternativa): void
    {
        try {
            $sql = "UPDATE alternativa_associacao 
                    SET IdAlternativaAssociada = :idAlternativaAssociada
                    WHERE IdAlternativa = :idAlternativa";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idAlternativaAssociada", $alternativa->getIdAlternativaAssociada());
            $stmt->bindValue(":idAlternativa", $alternativa->getIdAlternativa());
            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar alternativa do tipo associacao: " . $e->getMessage());
        }
    }

    private function mapearAlternativaAssociacao(array $dados): AlternativaAssociacao
    {
        $parAssociado = new class(
            (int) $dados["IdAssoc"],
            $dados["TextoAssoc"],
            'associacao'
        ) extends Alternativa {};

        if (isset($dados["IdQuestaoAssoc"]) && $dados["IdQuestaoAssoc"] !== null) {
            $parAssociado->setIdQuestao((int) $dados["IdQuestaoAssoc"]);
        }

        $alternativaPrincipal = new AlternativaAssociacao(
            (int) $dados["IdAlternativa"],
            $dados["Texto"],
            $parAssociado
        );

        if (isset($dados["IdQuestao"]) && $dados["IdQuestao"] !== null) {
            $alternativaPrincipal->setIdQuestao((int) $dados["IdQuestao"]);
        }

        return $alternativaPrincipal;
    }
}