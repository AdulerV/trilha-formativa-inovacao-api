<?php

declare(strict_types=1);

class MissaoDAO
{
    private PDO $conexao;
    private MissaoConteudoDAO $conteudoDAO;
    private MissaoAtividadeDAO $atividadeDAO;

    public function __construct(
        PDO $conexao,
        MissaoConteudoDAO $conteudoDAO,
        MissaoAtividadeDAO $atividadeDAO
    ) {
        $this->conexao = $conexao;
        $this->conteudoDAO = $conteudoDAO;
        $this->atividadeDAO = $atividadeDAO;
    }

    public function salvar(Missao $missao): void
    {
        try {
            $this->conexao->beginTransaction();

            $idMissao = $this->inserirMissaoBase($missao);
            $missao->setIdMissao($idMissao);

            $this->salvarEspecializacao($idMissao, $missao);

            $this->conexao->commit();
        } catch (Exception $e) {
            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }
            throw new Exception("Erro ao salvar missão: " . $e->getMessage(), 0, $e);
        }
    }

    private function inserirMissaoBase(Missao $missao): int
    {
        $sql = "INSERT INTO missao (
                    Titulo,
                    Pontuacao,
                    TipoMissao,
                    IdTematica
                ) VALUES (
                    :titulo,
                    :pontuacao,
                    :tipoMissao,
                    :idTematica
                )";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":titulo", $missao->getTitulo());
        $stmt->bindValue(":pontuacao", $missao->getPontuacao());
        $stmt->bindValue(":tipoMissao", $missao->getTipoMissao());
        $stmt->bindValue(":idTematica", $missao->getTematica()->getIdTematica());
        $stmt->execute();

        return (int) $this->conexao->lastInsertId();
    }

    private function salvarEspecializacao(int $idMissao, Missao $missao): void
    {
        if ($missao instanceof MissaoConteudo) {
            $this->conteudoDAO->salvar($idMissao, $missao);
            return;
        }

        if ($missao instanceof MissaoAtividade) {
            $this->atividadeDAO->salvar($idMissao, $missao);
            return;
        }
    }

    public function listar(): array
    {
        return array_merge(
            $this->conteudoDAO->listar(),
            $this->atividadeDAO->listar()
        );
    }

    public function buscarPorId(int $idMissao): ?Missao
    {
        try {
            $sql = "SELECT TipoMissao 
                    FROM missao 
                    WHERE IdMissao = :idMissao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idMissao", $idMissao);
            $stmt->execute();

            $tipo = $stmt->fetchColumn();

            if ($tipo === false) {
                return null;
            }

            return match (strtolower((string) $tipo)) {
                Missao::TIPO_ATIVIDADE => $this->atividadeDAO->buscarPorId($idMissao),
                Missao::TIPO_CONTEUDO => $this->conteudoDAO->buscarPorId($idMissao),
                default => throw new Exception("Tipo de missão inválido: '{$tipo}'")
            };
        } catch (PDOException $e) {
            throw new Exception("Erro ao encontrar missão especificada: " . $e->getMessage(), 0, $e);
        }
    }

    public function atualizar(Missao $missao): void
    {
        try {
            $this->conexao->beginTransaction();

            $sql = "UPDATE missao 
                    SET Titulo = :titulo, Pontuacao = :pontuacao, IdTematica = :idTematica
                    WHERE IdMissao = :id";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":titulo", $missao->getTitulo());
            $stmt->bindValue(":pontuacao", $missao->getPontuacao());
            $stmt->bindValue(":idTematica", $missao->getTematica()->getIdTematica());
            $stmt->bindValue(":id", $missao->getIdMissao());
            $stmt->execute();

            if ($missao instanceof MissaoAtividade) {
                $this->atividadeDAO->atualizar($missao);
            }

            if ($missao instanceof MissaoConteudo) {
                $this->conteudoDAO->atualizar($missao);
            }

            $this->conexao->commit();
        } catch (Exception $e) {
            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }
            throw new Exception("Erro ao atualizar missão: " . $e->getMessage(), 0, $e);
        }
    }

    public function deletar(int $idMissao): void
    {
        try {
            $sql = "DELETE FROM missao WHERE IdMissao = :idMissao";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idMissao", $idMissao);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao deletar missão: " . $e->getMessage(), 0, $e);
        }
    }

    public function verificarSeMissaoExiste(int $idMissao): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM missao WHERE IdMissao = :idMissao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idMissao", $idMissao);
            $stmt->execute();

            return ((int) $stmt->fetchColumn()) > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar existência da missão: " . $e->getMessage(), 0, $e);
        }
    }

    public function verificarSeTituloExiste(string $titulo): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM missao WHERE Titulo = :titulo";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":titulo", $titulo);
            $stmt->execute();

            return ((int) $stmt->fetchColumn()) > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar existência do título: " . $e->getMessage(), 0, $e);
        }
    }

    public function verificarTituloParaOutraMissao(string $titulo, int $idMissao): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM missao WHERE Titulo = :titulo AND IdMissao != :idMissao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":titulo", $titulo);
            $stmt->bindValue(":idMissao", $idMissao);
            $stmt->execute();

            return ((int) $stmt->fetchColumn()) > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar título para outra missão: " . $e->getMessage(), 0, $e);
        }
    }

    public function buscarTipoMissao(int $idMissao): ?string
    {
        try {
            $sql = "SELECT TipoMissao FROM missao WHERE IdMissao = :id";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":id", $idMissao);
            $stmt->execute();

            $resultado = $stmt->fetchColumn();
            return $resultado !== false ? (string) $resultado : null;
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar tipo da missão: " . $e->getMessage(), 0, $e);
        }
    }
}