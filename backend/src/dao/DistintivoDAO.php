<?php

class DistintivoDAO
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function salvar(Distintivo $distintivo): void
    {
        try {
            $sql = "INSERT INTO distintivo (Titulo, Pontuacao, NomeArquivo)
                VALUES (:titulo, :pontuacao, :nomeArquivo)";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":titulo", $distintivo->getTitulo());
            $stmt->bindValue(":pontuacao", $distintivo->getPontuacao());
            $stmt->bindValue(":nomeArquivo", $distintivo->getNomeArquivo());
            $stmt->execute();

            $distintivo->setIdDistintivo((int) $this->conexao->lastInsertId());
        } catch (PDOException) {
            throw new Exception("Erro ao salvar distintivo!");
        }
    }

    public function buscarPorId(int $idDistintivo): ?Distintivo
    {
        try {
            $sql = "SELECT * FROM distintivo WHERE IdDistintivo = :idDistintivo";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idDistintivo", $idDistintivo);
            $stmt->execute();

            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$registro) {
                return null;
            }

            return $this->mapearDistintivo($registro);
        } catch (PDOException) {
            throw new Exception("Erro ao buscar distintivo de ID igual a {$idDistintivo}");
        }
    }

    public function listar(): ?array
    {
        try {
            $sql = "SELECT * FROM distintivo";

            $stmt = $this->conexao->prepare($sql);
            $stmt->execute();

            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $distintivos = [];

            if (!$registros) {
                return null;
            }

            foreach ($registros as $registro) {
                $distintivos[] = $this->mapearDistintivo($registro);
            }

            return $distintivos;
        } catch (PDOException) {
            throw new Exception("Erro ao listar distintivos!");
        }
    }

    public function atualizar(Distintivo $distintivo)
    {
        try {
            $sql = "UPDATE distintivo 
            SET Titulo = :titulo, Pontuacao = :pontuacao, NomeArquivo = :nomeArquivo
            WHERE IdDistintivo = :idDistintivo";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":titulo", $distintivo->getTitulo());
            $stmt->bindValue(":pontuacao", $distintivo->getPontuacao());
            $stmt->bindValue(":nomeArquivo", $distintivo->getNomeArquivo());
            $stmt->bindValue(":idDistintivo", $distintivo->getIdDistintivo());

            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao atualizar distintivo!");
        }
    }

    public function deletar(int $idDistintivo): void
    {
        try {
            $sql = "DELETE FROM distintivo WHERE IdDistintivo = :idDistintivo";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idDistintivo", $idDistintivo);
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao deletar distintivo!");
        }
    }

    public function getPontuacaoMaxima(): float
    {
        $sql = "SELECT COUNT(*) FROM tematica";

        $stmt = $this->conexao->prepare($sql);
        $stmt->execute();

        $numeroTrilhas = $stmt->fetchColumn();
        $numeroTrilhas = ($numeroTrilhas > 0 ? $numeroTrilhas : 1);

        return ((9999 / $numeroTrilhas) * 0.1);
    }

    public function verificarSeDistintivoExiste(int $idDistintivo): bool
    {
        $sql = "SELECT count(*) FROM distintivo WHERE IdDistintivo = :idDistintivo";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":idDistintivo", $idDistintivo);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function verificarSeTituloExiste(string $titulo): bool
    {
        $sql = "SELECT COUNT(*) FROM distintivo WHERE Titulo = :titulo";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":titulo", $titulo);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function verificarSeNomeArquivoExiste(string $nomeArquivo): bool
    {
        $sql = "SELECT COUNT(*) FROM distintivo 
                WHERE NomeArquivo = :nomeArquivo 
                AND NomeArquivo NOT LIKE 'badgeDefault.svg'";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":nomeArquivo", $nomeArquivo);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function verificarTituloParaOutroDistintivo(string $titulo, int $idDistintivo): bool
    {
        $sql = "SELECT COUNT(*) FROM distintivo WHERE LOWER(Titulo) = LOWER(:titulo) AND IdDistintivo != :idDistintivo";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":titulo", $titulo);
        $stmt->bindValue(":idDistintivo", $idDistintivo);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function verificarNomeArquivoParaOutroDistintivo(string $nomeArquivo, int $idDistintivo): bool
    {
        $sql = "SELECT COUNT(*) FROM distintivo WHERE LOWER(NomeArquivo) = LOWER(:nomeArquivo) AND IdDistintivo != :idDistintivo AND NomeArquivo NOT LIKE 'badgeDefault.svg'";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":nomeArquivo", $nomeArquivo);
        $stmt->bindValue(":idDistintivo", $idDistintivo);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function mapearDistintivo(array $registro): Distintivo
    {
        return new Distintivo(
            (int) $registro["IdDistintivo"],
            $registro["Titulo"],
            (float) $registro["Pontuacao"],
            $registro["NomeArquivo"]
        );
    }
}
