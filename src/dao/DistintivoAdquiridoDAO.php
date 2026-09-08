<?php
class DistintivoAdquiridoDAO
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function salvar(DistintivoAdquirido $distintivo): void
    {
        try {
            $sql = "INSERT INTO distintivo_adquirido (IdDistintivo, IdUsuario) 
            VALUES (:idDistintivo, :idUsuario)";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idDistintivo", $distintivo->getDistintivo()->getIdDistintivo());
            $stmt->bindValue(":idUsuario", $distintivo->getUsuario()->getIdUsuario());
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao salvar distintivo adquirido!");
        }
    }

    public function buscarPorId(int $idUsuario, int $idDistintivo): DistintivoAdquirido
    {
        try {
            $sql = "SELECT
            u.IdUsuario,
            u.Nome,
            u.NomeAventureiro,
            u.CorreioEletronico,
            u.DataNascimento,
            u.DataNascimento,
            u.PossuiConhecimento,
            u.PrimeiroAcesso,
            u.Admin,
            u.HashSenha,
            u.FotoPerfil,

            o.IdOcupacao,
            o.Titulo AS TituloOcupacao,

            d.IdDistintivo,
            d.Titulo AS TituloDistintivo,
            d.Pontuacao,
            d.NomeArquivo

            FROM distintivo_adquirido da

            INNER JOIN usuario u
            ON da.IdUsuario = u.IdUsuario

            INNER JOIN ocupacao o
            ON u.IdOcupacao = o.IdOcupacao

            INNER JOIN distintivo d
            ON da.IdDistintivo = d.IdDistintivo

            WHERE da.IdUsuario = :idUsuario
            AND da.IdDistintivo = :idDistintivo";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idUsuario", $idUsuario);
            $stmt->bindValue(":idDistintivo", $idDistintivo);
            $stmt->execute();

            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            $distintivo =  $this->mapearDistintivoAdquirido($registro);

            return $distintivo;
        } catch (PDOException) {
            throw new Exception("Erro ao buscar distintivo adquirido!");
        }
    }

    public function listar(): array
    {
        $sql = "SELECT
            u.IdUsuario,
            u.Nome,
            u.NomeAventureiro,
            u.CorreioEletronico,
            u.DataNascimento,
            u.PossuiConhecimento,
            u.PrimeiroAcesso,
            u.Admin,
            u.HashSenha,
            u.FotoPerfil,

            o.IdOcupacao,
            o.Titulo AS TituloOcupacao,

            d.IdDistintivo,
            d.Titulo AS TituloDistintivo,
            d.Pontuacao,
            d.NomeArquivo

            FROM distintivo_adquirido da

            INNER JOIN usuario u
            ON da.IdUsuario = u.IdUsuario

            INNER JOIN ocupacao o
            ON u.IdOcupacao = o.IdOcupacao

            INNER JOIN distintivo d
            ON da.IdDistintivo = d.IdDistintivo";

        $stmt = $this->conexao->prepare($sql);
        $stmt->execute();

        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $distintivos = [];

        foreach ($registros as $registro) {
            $distintivos[] = $this->mapearDistintivoAdquirido($registro);
        }

        return $distintivos;
    }

    public function listarPorUsuario(int $idUsuario): array
    {
        try {
            $sql = "SELECT
            u.IdUsuario,
            u.Nome,
            u.NomeAventureiro,
            u.CorreioEletronico,
            u.DataNascimento,
            u.PossuiConhecimento,
            u.PrimeiroAcesso,
            u.Admin,
            u.HashSenha,
            u.FotoPerfil,

            o.IdOcupacao,
            o.Titulo AS TituloOcupacao,

            d.IdDistintivo,
            d.Titulo AS TituloDistintivo,
            d.Pontuacao,
            d.NomeArquivo

            FROM distintivo_adquirido da

            INNER JOIN usuario u
            ON da.IdUsuario = u.IdUsuario

            INNER JOIN ocupacao o
            ON u.IdOcupacao = o.IdOcupacao

            INNER JOIN distintivo d
            ON da.IdDistintivo = d.IdDistintivo

            WHERE da.IdUsuario = :idUsuario";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idUsuario", $idUsuario);
            $stmt->execute();

            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $distintivos = [];

            foreach ($registros as $registro) {
                $distintivos[] = $this->mapearDistintivoAdquirido($registro);
            }
            return $distintivos;
        } catch (PDOException) {
            throw new Exception("Erro ao listar distintivos adquiridos pelo usuário de ID {$idUsuario}");
        }
    }

    /*     public function atualizar(DistintivoAdquirido $distintivoAtual, DistintivoAdquirido $novoDistintivo): void
    {
        try {
            $sql = "UPDATE distintivo_adquirido
            SET IdDistintivo = :novoIdDistintivo
            WHERE IdUsuario = :idUsuario
            AND IdDistintivo = :idDistintivoAtual";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":novoIdDistintivo", $novoDistintivo->getDistintivo()->getIdDistintivo());
            $stmt->bindValue(":idUsuario", $distintivoAtual->getUsuario()->getIdUsuario());
            $stmt->bindValue(":idDistintivoAtual", $distintivoAtual->getDistintivo()->getIdDistintivo());
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao atualizar distintivo adquirido!");
        }
    } */

    public function deletar(int $idUsuario, int $idDistintivo): void
    {
        try {
            $sql = "DELETE FROM distintivo_adquirido
            WHERE IdUsuario = :idUsuario
            AND IdDistintivo = :idDistintivo";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idUsuario", $idUsuario);
            $stmt->bindValue(":idDistintivo", $idDistintivo);
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception(
                "Erro ao deletar distintivo adquirido!"
            );
        }
    }

    public function verificarSeDistintivoAdquiridoExiste(int $idUsuario, int $idDistintivo): bool
    {
        $sql = "SELECT COUNT(*) FROM distintivo_adquirido WHERE 
        IdUsuario = :idUsuario AND IdDistintivo = :idDistintivo";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":idUsuario", $idUsuario);
        $stmt->bindValue(":idDistintivo", $idDistintivo);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function mapearDistintivoAdquirido(array $registro): DistintivoAdquirido
    {

        $ocupacao = new Ocupacao(
            (int) $registro["IdOcupacao"],
            $registro["TituloOcupacao"]
        );

        $usuario = Usuario::rehidratar(
            (int) $registro["IdUsuario"],
            $registro["Nome"],
            $registro["NomeAventureiro"],
            $registro["CorreioEletronico"],
            $registro["DataNascimento"],
            (bool) $registro["PossuiConhecimento"],
            (bool) $registro["PrimeiroAcesso"],
            (bool) $registro["Admin"],
            $ocupacao,
            $registro["FotoPerfil"] ?? null
        );

        $distintivo = new Distintivo(
            (int) $registro["IdDistintivo"],
            $registro["TituloDistintivo"],
            (float) $registro["Pontuacao"],
            $registro["NomeArquivo"]
        );

        return new DistintivoAdquirido(
            $usuario,
            $distintivo
        );
    }
}
