<?php

declare(strict_types=1);

class AlternativaDAO
{
    private PDO $conexao;
    private AlternativaOrdenacaoDAO $ordenacaoDAO;
    private AlternativaAssociacaoDAO $associacaoDAO;
    private AlternativaMultiplaEscolhaDAO $multiplaEscolhaDAO;

    public function __construct(
        PDO $conexao,
        AlternativaOrdenacaoDAO $ordenacaoDAO,
        AlternativaAssociacaoDAO $associacaoDAO,
        AlternativaMultiplaEscolhaDAO $multiplaEscolhaDAO
    ) {
        $this->conexao = $conexao;
        $this->ordenacaoDAO = $ordenacaoDAO;
        $this->associacaoDAO = $associacaoDAO;
        $this->multiplaEscolhaDAO = $multiplaEscolhaDAO;
    }

    public function salvar(Alternativa $alternativa): void
    {
        try {
            $this->conexao->beginTransaction();

            $idAlternativa = $this->inserirAlternativaBase($alternativa);

            $this->salvarEspecializacao($idAlternativa, $alternativa);

            $this->conexao->commit();
        } catch (PDOException $e) {
            $this->conexao->rollBack();
            throw new Exception("Erro ao salvar alternativa completa: " . $e->getMessage());
        }
    }

    private function inserirAlternativaBase(Alternativa $alternativa): int
    {
        $sql = "INSERT INTO alternativa (
                    Texto,
                    TipoAlternativa,
                    IdQuestao
                ) VALUES (
                    :texto,
                    :tipoAlternativa,
                    :idQuestao
                )";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":texto", $alternativa->getTexto());
        $stmt->bindValue(":tipoAlternativa", $alternativa->getTipoAlternativa());
        $stmt->bindValue(":idQuestao", $alternativa->getIdQuestao());
        $stmt->execute();

        return (int) $this->conexao->lastInsertId();
    }

    private function salvarEspecializacao(
        int $idAlternativa,
        Alternativa $alternativa
    ): void {

        if ($alternativa instanceof AlternativaOrdenacao) {
            $this->ordenacaoDAO->salvar($idAlternativa, $alternativa);
            return;
        }

        if ($alternativa instanceof AlternativaAssociacao) {

            $associada = $alternativa->getAlternativaAssociada();

            if ($associada->getIdAlternativa() === null) {
                $associada->setIdQuestao($alternativa->getIdQuestao());
                $idAssociada = $this->inserirAlternativaBase($associada);
                $associada->setIdAlternativa($idAssociada);
            }

            $this->associacaoDAO->salvar(
                $idAlternativa,
                $alternativa
            );

            return;
        }

        if ($alternativa instanceof AlternativaMultiplaEscolha) {
            $this->multiplaEscolhaDAO->salvar($idAlternativa, $alternativa);
            return;
        }

        throw new Exception("Tipo de especialização de alternativa não suportado!");
    }

    public function buscarPorId(int $idAlternativa): ?Alternativa
    {
        try {
            $sql = "SELECT TipoAlternativa FROM alternativa WHERE IdAlternativa = :id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":id", $idAlternativa, PDO::PARAM_INT);
            $stmt->execute();

            $tipo = $stmt->fetchColumn();

            if (!$tipo) {
                return null;
            }

            return match ($tipo) {
                Alternativa::TIPO_ORDENACAO => $this->ordenacaoDAO->buscarPorId($idAlternativa),
                Alternativa::TIPO_ASSOCIACAO => $this->associacaoDAO->buscarPorId($idAlternativa),
                Alternativa::TIPO_MULTIPLA_ESCOLHA => $this->multiplaEscolhaDAO->buscarPorId($idAlternativa),
                default => throw new Exception("Tipo de alternativa desconhecido no banco de dados!")
            };
        } catch (PDOException) {
            throw new Exception("Erro ao buscar alternativa指定!");
        }
    }

    public function listar(): array
    {
        return array_merge(
            $this->multiplaEscolhaDAO->listar(),
            $this->ordenacaoDAO->listar(),
            $this->associacaoDAO->listar()
        );
    }

    public function listarPorQuestao(int $idQuestao): array
    {
        try {
            $sql = "SELECT TipoAlternativa
                FROM ALTERNATIVA
                WHERE IdQuestao = :idQuestao
                LIMIT 1";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idQuestao", $idQuestao, PDO::PARAM_INT);
            $stmt->execute();

            $tipo = $stmt->fetchColumn();

            if (!$tipo) {
                return [];
            }

            $tipo = strtolower(trim($tipo));

            return match ($tipo) {
                Alternativa::TIPO_MULTIPLA_ESCOLHA =>
                $this->multiplaEscolhaDAO->listarPorQuestao($idQuestao),

                Alternativa::TIPO_ORDENACAO =>
                $this->ordenacaoDAO->listarPorQuestao($idQuestao),

                Alternativa::TIPO_ASSOCIACAO =>
                $this->associacaoDAO->listarPorQuestao($idQuestao),

                default => throw new Exception(
                    "Tipo de alternativa inválido! Tipos aceitos: "
                        . Alternativa::TIPO_ORDENACAO . ", "
                        . Alternativa::TIPO_ASSOCIACAO . ", "
                        . Alternativa::TIPO_MULTIPLA_ESCOLHA
                )
            };
        } catch (PDOException $e) {
            throw new Exception("Erro ao listar alternativas da questão: " . $e->getMessage());
        }
    }

    public function atualizar(Alternativa $alternativa): void
    {
        try {
            $this->conexao->beginTransaction();

            $sql = "UPDATE alternativa 
                    SET Texto = :texto
                    WHERE IdAlternativa = :id";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":texto", $alternativa->getTexto());
            $stmt->bindValue(":id", $alternativa->getIdAlternativa());
            $stmt->execute();

            $this->atualizarEspecializacao($alternativa);

            $this->conexao->commit();
        } catch (PDOException $e) {
            $this->conexao->rollBack();
            throw new Exception("Erro ao atualizar alternativa completa: " . $e->getMessage());
        }
    }

    private function atualizarEspecializacao(Alternativa $alternativa): void
    {
        if ($alternativa instanceof AlternativaOrdenacao) {
            $this->ordenacaoDAO->atualizar($alternativa);
            return;
        }

        if ($alternativa instanceof AlternativaAssociacao) {
            $sql = "UPDATE alternativa 
                    SET Texto = :texto
                    WHERE IdAlternativa = :id";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":texto", $alternativa->getAlternativaAssociada()->getTexto());
            $stmt->bindValue(":id", $alternativa->getAlternativaAssociada()->getIdAlternativa());
            $stmt->execute();

            $this->associacaoDAO->atualizar($alternativa);
            return;
        }

        if ($alternativa instanceof AlternativaMultiplaEscolha) {
            $this->multiplaEscolhaDAO->atualizar($alternativa);
            return;
        }
    }

    public function deletar(int $idAlternativa): void
    {
        try {
            $sql = "DELETE FROM alternativa WHERE IdAlternativa = :id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":id", $idAlternativa);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao deletar alternativa: " . $e->getMessage());
        }
    }

    public function verificarSeAlternativaExistePorId(int $idAlternativa): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM alternativa WHERE IdAlternativa = :idAlternativa";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idAlternativa", $idAlternativa, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException) {
            throw new Exception("Erro ao verificar a existencia da alternativa!");
        }
    }

    public function verificarSeTextoExiste(int $idQuestao, string $texto): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM alternativa WHERE IdQuestao = :idQuestao AND Texto = :texto";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idQuestao", $idQuestao, PDO::PARAM_INT);
            $stmt->bindValue(":texto", trim($texto), PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException) {
            throw new Exception("Erro ao verificar duplicidade de texto da alternativa!");
        }
    }

    public function verificarSeMesmoTipoAlternativa(int $idAlternativa, string $tipo): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM alternativa WHERE IdAlternativa = :idAlternativa AND TipoAlternativa = :tipo";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idAlternativa", $idAlternativa, PDO::PARAM_INT);
            $stmt->bindValue(":tipo", $tipo, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException) {
            throw new Exception("Erro ao verificar mesmo tipo de alternativa!");
        }
    }

    public function verificarSeMesmoTipoOutrasAlternativas(int $idQuestao, string $tipo): bool
    {
        try {
            $sql = "SELECT TipoAlternativa FROM alternativa WHERE IdQuestao = :idQuestao";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idQuestao", $idQuestao, PDO::PARAM_INT);
            $stmt->execute();

            $tipoDemais = $stmt->fetchColumn();

            if ($tipoDemais !== false) {
                return $tipo === $tipoDemais;
            }
            return true;
        } catch (PDOException) {
            throw new Exception("Erro ao verificar mesmo tipo de alternativa!");
        }
    }

    public function verificarSeMesmoNumeroSequenciaOutrasAlternativas(
        int $idQuestao,
        int $numeroSequencia
    ): bool {
        try {
            $sql = "SELECT COUNT(*)
                FROM alternativa_ordenacao ao
                INNER JOIN alternativa a
                    ON ao.IdAlternativa = a.IdAlternativa
                WHERE a.IdQuestao = :idQuestao
                  AND ao.NumeroSequencia = :numeroSequencia";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idQuestao", $idQuestao, PDO::PARAM_INT);
            $stmt->bindValue(":numeroSequencia", $numeroSequencia, PDO::PARAM_INT);
            $stmt->execute();

            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException) {
            throw new Exception("Erro ao verificar número de sequência!");
        }
    }

    public function verificarTextoParaOutraAlternativa(
        int $idQuestao,
        string $texto,
        ?int $idAlternativa = null
    ): bool {
        try {
            $sql = "SELECT COUNT(*)
                FROM alternativa
                WHERE IdQuestao = :idQuestao
                  AND Texto = :texto";

            if ($idAlternativa !== null) {
                $sql .= " AND IdAlternativa <> :idAlternativa";
            }

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idQuestao", $idQuestao);
            $stmt->bindValue(":texto", trim($texto));

            if ($idAlternativa !== null) {
                $stmt->bindValue(":idAlternativa", $idAlternativa);
            }

            $stmt->execute();

            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException) {
            throw new Exception(
                "Erro ao validar atualização de texto para outra alternativa!"
            );
        }
    }
}
