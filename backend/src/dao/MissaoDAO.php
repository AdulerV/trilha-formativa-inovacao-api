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
        } catch (PDOException) {
            $this->conexao->rollBack();
            throw new Exception("Erro ao salvar missão!");
        }
    }

    private function inserirMissaoBase(Missao $missao): int
    {
        try {
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

            $idMissao = (int) $this->conexao->lastInsertId();

            return $idMissao;
        } catch (PDOException) {
            throw new Exception("Erro ao salvar missão base!");
        }
    }

    private function salvarEspecializacao(int $idMissao, Missao $missao): void
    {
        try {
            if ($missao instanceof MissaoConteudo) {
                $this->conteudoDAO->salvar($idMissao, $missao);
                return;
            }

            if ($missao instanceof MissaoAtividade) {
                $this->atividadeDAO->salvar($idMissao, $missao);
                return;
            }
        } catch (PDOException) {
            throw new Exception("Erro ao salvar especialização da missão base!");
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

            return match (strtolower($tipo)) {
                Missao::TIPO_ATIVIDADE => $this->atividadeDAO->buscarPorId($idMissao),
                Missao::TIPO_CONTEUDO => $this->conteudoDAO->buscarPorId($idMissao),
                default => throw new Exception("Tipo de missão inválida!")
            };
        } catch (PDOException) {
            throw new Exception("Erro ao encontrar missão especificada!");
        }
    }

    public function atualizar(Missao $missao)
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
        } catch (PDOException) {
            $this->conexao->rollBack();
            throw new Exception("Erro ao atualizar missão!");
        }
    }

    public function deletar(int $idMissao)
    {
        /* Supondo que há delete cascade no Banco de Dados */

        try {
            $sql = "DELETE FROM missao WHERE IdMissao = :idMissao";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idMissao", $idMissao);
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao deletar missão!");
        }
    }

    public function verificarSeMissaoExiste(int $idMissao): bool
    {
        $sql = "SELECT COUNT(*) FROM missao WHERE IdMissao = :idMissao";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":idMissao", $idMissao);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function verificarSeTituloExiste(string $titulo): bool
    {
        $sql = "SELECT COUNT(*) FROM missao WHERE Titulo = :titulo";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":titulo", $titulo);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function verificarTituloParaOutraMissao(string $titulo, int $idMissao): bool
    {
        $sql = "SELECT COUNT(*) FROM missao WHERE Titulo = :titulo AND IdMissao != :idMissao";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":titulo", $titulo);
        $stmt->bindValue(":idMissao", $idMissao);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function buscarTipoMissao(int $idMissao): ?string
    {
        $sql = "SELECT TipoMissao FROM missao WHERE IdMissao = :id";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":id", $idMissao);
        $stmt->execute();

        return $stmt->fetchColumn() ?: null;
    }
}
