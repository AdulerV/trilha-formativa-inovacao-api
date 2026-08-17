<?php

declare(strict_types=1);

class AlternativaMultiplaEscolhaDAO
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function salvar(int $idAlternativa, AlternativaMultiplaEscolha $alternativa): void
    {
        try {
            $sql = "INSERT INTO ALTERNATIVA_MULTIPLA_ESCOLHA (IdAlternativa, TipoMultiplaEscolha, Correta)
                    VALUES (:idAlternativa, :tipoMultiplaEscolha, :correta)";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idAlternativa", $idAlternativa, PDO::PARAM_INT);
            $stmt->bindValue(":tipoMultiplaEscolha", $alternativa->getTipoMultiplaEscolha(), PDO::PARAM_STR);
            $stmt->bindValue(":correta", $alternativa->isCorreta(), PDO::PARAM_BOOL); // Certifique-se de que o método existe na Entidade
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao salvar alternativa do tipo múltipla escolha!");
        }
    }

    public function listar(): array
    {
        try {
            $sql = "SELECT 
                        a.IdAlternativa,
                        a.Texto,
                        am.Correta,
                        a.IdQuestao,
                        am.TipoMultiplaEscolha
                    FROM ALTERNATIVA_MULTIPLA_ESCOLHA AS am
                    INNER JOIN alternativa AS a ON a.IdAlternativa = am.IdAlternativa";

            $stmt = $this->conexao->prepare($sql);
            $stmt->execute();

            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $alternativas = [];

            foreach ($registros as $registro) {
                $alternativas[] = $this->mapearAlternativaMultiplaEscolha($registro);
            }

            return $alternativas;
        } catch (PDOException) {
            throw new Exception("Erro ao listar alternativas do tipo múltipla escolha!");
        }
    }

    public function listarPorQuestao(int $idQuestao): array
    {
        try {
            $sql = "SELECT 
                        a.IdAlternativa,
                        a.Texto,
                        am.Correta,
                        a.IdQuestao,
                        am.TipoMultiplaEscolha
                    FROM ALTERNATIVA_MULTIPLA_ESCOLHA AS am
                    INNER JOIN alternativa AS a ON a.IdAlternativa = am.IdAlternativa
                    WHERE a.IdQuestao = :idQuestao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idQuestao", $idQuestao, PDO::PARAM_INT);
            $stmt->execute();

            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $alternativas = [];

            foreach ($registros as $registro) {
                $alternativas[] = $this->mapearAlternativaMultiplaEscolha($registro);
            }

            return $alternativas;
        } catch (PDOException) {
            throw new Exception("Erro ao listar alternativas de múltipla escolha para a questão especificada!");
        }
    }

    public function buscarPorId(int $idAlternativa): AlternativaMultiplaEscolha
    {
        try {
            $sql = "SELECT 
                        a.IdAlternativa,
                        a.Texto,
                        am.Correta,
                        a.IdQuestao,
                        am.TipoMultiplaEscolha
                    FROM ALTERNATIVA_MULTIPLA_ESCOLHA AS am
                    INNER JOIN alternativa AS a ON a.IdAlternativa = am.IdAlternativa
                    WHERE am.IdAlternativa = :idAlternativa";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idAlternativa", $idAlternativa, PDO::PARAM_INT);
            $stmt->execute();

            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$registro) {
                throw new Exception("Alternativa de múltipla escolha não encontrada!");
            }

            return $this->mapearAlternativaMultiplaEscolha($registro);
        } catch (PDOException) {
            throw new Exception("Erro ao encontrar alternativa do tipo múltipla escolha especificada!");
        }
    }

    public function atualizar(AlternativaMultiplaEscolha $alternativa): void
    {
        try {
            $sql = "UPDATE ALTERNATIVA_MULTIPLA_ESCOLHA 
                    SET TipoMultiplaEscolha = :tipoMultiplaEscolha,
                        Correta = :correta
                    WHERE IdAlternativa = :idAlternativa";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":tipoMultiplaEscolha", $alternativa->getTipoMultiplaEscolha(), PDO::PARAM_STR);
            $stmt->bindValue(":correta", $alternativa->isCorreta(), PDO::PARAM_BOOL);
            $stmt->bindValue(":idAlternativa", $alternativa->getIdAlternativa(), PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao atualizar alternativa do tipo múltipla escolha!");
        }
    }

    private function mapearAlternativaMultiplaEscolha(array $dados): AlternativaMultiplaEscolha
    {
        $alternativa = new AlternativaMultiplaEscolha(
            (int) $dados["IdAlternativa"],
            $dados["Texto"],
            (bool) $dados["Correta"],
            $dados["TipoMultiplaEscolha"]
        );

        if (isset($dados["IdQuestao"]) && $dados["IdQuestao"] !== null) {
            $alternativa->setIdQuestao((int) $dados["IdQuestao"]);
        }

        return $alternativa;
    }
}