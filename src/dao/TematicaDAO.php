<?php

declare(strict_types=1);

class TematicaDAO
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function salvar(Tematica $tematica): void
    {
        try {
            $sql = "INSERT INTO Tematica (Titulo) VALUES (:titulo)";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":titulo", $tematica->getTitulo());
            $stmt->execute();

            $tematica->setIdTematica((int) $this->conexao->lastInsertId());
        } catch (PDOException) {
            throw new Exception("Erro ao criar uma nova temática!");
        }
    }

    public function buscarPorId(int $idTematica): ?Tematica
    {
        try {
            $sql = "SELECT * FROM Tematica WHERE IdTematica = :idTematica";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idTematica", $idTematica);
            $stmt->execute();

            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            $tematica = $this->mapearTematica($registro);

            return $tematica;
        } catch (PDOException) {
            throw new Exception("Erro ao buscar a temática de ID igual a {$idTematica}");
        }
    }

    public function listar(): array
    {
        try {
            $sql = "SELECT * FROM Tematica";

            $stmt = $this->conexao->prepare($sql);
            $stmt->execute();

            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $tematicas = [];

            foreach ($registros as $registro) {
                $tematicas[] = $this->mapearTematica($registro);
            }
            return $tematicas;
        } catch (PDOException) {
            throw new Exception("Erro ao listar as temáticas!");
        }
    }

    public function atualizar(Tematica $tematica)
    {
        try {
            $sql = "UPDATE Tematica SET Titulo = :titulo WHERE IdTematica = :idTematica";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idTematica", $tematica->getIdTematica());
            $stmt->bindValue(":titulo", $tematica->getTitulo());
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao atualizar a temática!");
        }
    }

    public function deletar(int $idTematica)
    {
        try {
            $sql = "DELETE FROM Tematica WHERE IdTematica = :idTematica";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idTematica", $idTematica);
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao deletar a temática de ID igual a {$idTematica}");
        }
    }

    public function verificarSeTematicaExiste(int $idTematica): bool
    {
        $sql = "SELECT COUNT(*) FROM Tematica WHERE IdTematica = :idTematica";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":idTematica", $idTematica);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function verificarSeTituloExiste(string $titulo): bool
    {
        $sql = "SELECT COUNT(*) FROM Tematica WHERE Titulo = :titulo";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":titulo", $titulo);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function verificarTituloParaOutraTematica(string $titulo, int $idTematica): bool
    {
        $sql = "SELECT COUNT(*) FROM tematica WHERE Titulo = :titulo AND IdTematica != :idTematica";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":titulo", $titulo);
        $stmt->bindValue(":idTematica", $idTematica);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function mapearTematica(array $registro): Tematica
    {
        $tematica = new Tematica(
            (int) $registro["IdTematica"],
            $registro["Titulo"]
        );

        return $tematica;
    }
}
