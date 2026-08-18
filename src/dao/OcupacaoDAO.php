<?php

declare(strict_types=1);

class OcupacaoDAO
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function salvar(Ocupacao $ocupacao): void
    {
        try {
            $sql = "INSERT INTO ocupacao (Titulo) VALUES (:titulo)";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":titulo", $ocupacao->getTitulo());
            $stmt->execute();

            $ocupacao->setIdOcupacao((int) $this->conexao->lastInsertId());
        } catch (PDOException) {
            throw new Exception("Erro ao criar uma nova ocupação!");
        }
    }

    public function buscarPorId(int $idOcupacao): Ocupacao
    {
        try {
            $sql = "SELECT * FROM ocupacao WHERE IdOcupacao = :idOcupacao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idOcupacao", $idOcupacao);
            $stmt->execute();

            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            $ocupacao = $this->mapearOcupacao($registro);

            return $ocupacao;
        } catch (PDOException) {
            throw new Exception("Erro ao buscar a ocupação de ID igual a {$idOcupacao}");
        }
    }

    public function listar(): array
    {
        try {
            $sql = "SELECT * FROM ocupacao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->execute();

            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $ocupacoes = [];

            foreach ($registros as $registro) {
                $ocupacoes[] = $this->mapearOcupacao($registro);
            }
            return $ocupacoes;
        } catch (PDOException) {
            throw new Exception("Erro ao listar as ocupações!");
        }
    }

    public function atualizar(Ocupacao $ocupacao): void
    {
        try {
            $sql = "UPDATE ocupacao SET Titulo = :titulo WHERE IdOcupacao = :idOcupacao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idOcupacao", $ocupacao->getIdOcupacao());
            $stmt->bindValue(":titulo", $ocupacao->getTitulo());
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao atualizar a ocupação!");
        }
    }

    public function deletar(int $idOcupacao): void
    {
        try {
            $sql = "DELETE FROM ocupacao WHERE IdOcupacao = :idOcupacao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idOcupacao", $idOcupacao);
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao deletar a ocupação de ID igual a {$idOcupacao}");
        }
    }

    public function verificarSeOcupacaoExiste(int $idOcupacao): bool
    {
        $sql = "SELECT COUNT(*) FROM ocupacao WHERE IdOcupacao = :idOcupacao";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":idOcupacao", $idOcupacao);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function verificarSeTituloExiste(string $titulo): bool
    {
        $sql = "SELECT COUNT(*) FROM ocupacao WHERE LOWER(Titulo) = LOWER(:titulo)";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":titulo", $titulo);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function verificarTituloParaOutraOcupacao(string $titulo, int $idOcupacao): bool
    {
        $sql = "SELECT COUNT(*) FROM ocupacao WHERE LOWER(Titulo) = LOWER(:titulo) AND IdOcupacao != :idOcupacao";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":titulo", $titulo);
        $stmt->bindValue(":idOcupacao", $idOcupacao);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function mapearOcupacao(array $registro): Ocupacao
    {
        $ocupacao = new Ocupacao(
            (int) $registro["IdOcupacao"],
            $registro["Titulo"]
        );

        return $ocupacao;
    }
}
