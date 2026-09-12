<?php

declare(strict_types=1);

class UsuarioDAO
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function salvar(Usuario $usuario): void
    {
        try {
            $sql = "INSERT INTO usuario (Nome, NomeAventureiro, CorreioEletronico, DataNascimento, PossuiConhecimento, PrimeiroAcesso, HashSenha, IdOcupacao) VALUES (:nome, :nomeAventureiro, :correioEletronico, :dataNascimento, :possuiConhecimento, :primeiroAcesso, :hashSenha, :idOcupacao)";

            $stmt = $this->conexao->prepare($sql);

            $stmt->bindValue(":nome", $usuario->getNomeUsuario());
            $stmt->bindValue(":nomeAventureiro", $usuario->getNomeAventureiro());
            $stmt->bindValue(":correioEletronico", $usuario->getCorreioEletronico());
            $stmt->bindValue(":dataNascimento", $usuario->getDataNascimento()?->format("Y-m-d"));
            $stmt->bindValue(":possuiConhecimento", (int) $usuario->isPossuiConhecimento());
            $stmt->bindValue(":primeiroAcesso", (int) $usuario->isPrimeiroAcesso());
            $stmt->bindValue(":hashSenha", $usuario->getSenha());
            $stmt->bindValue(":idOcupacao", $usuario->getOcupacao()->getIdOcupacao());

            $stmt->execute();

            $usuario->setIdUsuario((int) $this->conexao->lastInsertId());
        } catch (PDOException $e) {
            throw new Exception("Erro ao inserir usuário: " . $e->getMessage());
        }
    }

    public function buscarPorId(int $idUsuario): ?Usuario
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
            u.FotoPerfil,
            u.Admin,
            u.HashSenha,
            o.IdOcupacao,
            o.Titulo
            FROM usuario AS u 
            INNER JOIN ocupacao AS o 
            ON u.IdOcupacao = o.IdOcupacao 
            WHERE u.IdUsuario = :idUsuario";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idUsuario", $idUsuario);
            $stmt->execute();

            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$registro) {
                return null;
            }

            return $this->mapearUsuario($registro);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar usuário com ID igual a {$idUsuario}: " . $e->getMessage());
        }
    }

    public function buscarPorCorreioEletronico(string $correioEletronico): ?Usuario
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
            u.FotoPerfil,
            u.Admin,
            u.HashSenha,
            o.IdOcupacao,
            o.Titulo
            FROM usuario AS u 
            INNER JOIN ocupacao AS o 
            ON u.IdOcupacao = o.IdOcupacao 
            WHERE u.CorreioEletronico = :correioEletronico";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":correioEletronico", $correioEletronico);
            $stmt->execute();

            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$registro) {
                return null;
            }

            return $this->mapearUsuario($registro);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar usuário pelo e-mail: {$correioEletronico}: " . $e->getMessage());
        }
    }

    public function listar(): array
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
            u.FotoPerfil,
            u.Admin,
            u.HashSenha,
            o.IdOcupacao,
            o.Titulo
            FROM usuario AS u 
            INNER JOIN ocupacao AS o 
            ON u.IdOcupacao = o.IdOcupacao AND u.Admin <> 1";

            $stmt = $this->conexao->prepare($sql);
            $stmt->execute();

            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $usuarios = [];

            foreach ($registros as $registro) {
                $usuarios[] = $this->mapearUsuario($registro);
            }

            return $usuarios;
        } catch (PDOException $e) {
            throw new Exception("Erro ao listar usuários: " . $e->getMessage());
        }
    }

    public function atualizar(Usuario $usuario): void
    {
        try {
            $alterarSenha = $usuario->temSenhaDefinida();

            $sql = "UPDATE usuario SET
                Nome = :nome,
                NomeAventureiro = :nomeAventureiro,
                CorreioEletronico = :correioEletronico,
                DataNascimento = :dataNascimento,
                PossuiConhecimento = :possuiConhecimento,
                PrimeiroAcesso = :primeiroAcesso,"
                . ($alterarSenha ? "\n                HashSenha = :hashSenha," : "")
                . "
                IdOcupacao = :idOcupacao
                WHERE IdUsuario = :idUsuario";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idUsuario", $usuario->getIdUsuario());
            $stmt->bindValue(":nome", $usuario->getNomeUsuario());
            $stmt->bindValue(":nomeAventureiro", $usuario->getNomeAventureiro());
            $stmt->bindValue(":correioEletronico", $usuario->getCorreioEletronico());
            $stmt->bindValue(":dataNascimento", $usuario->getDataNascimento()?->format("Y-m-d"));

            $possuiConhecimento = $usuario->isPossuiConhecimento();

            $stmt->bindValue(
                ":possuiConhecimento",
                $possuiConhecimento === null ? null : (int) $possuiConhecimento,
                $possuiConhecimento === null ? PDO::PARAM_NULL : PDO::PARAM_INT
            );

            $stmt->bindValue(":primeiroAcesso", (int) $usuario->isPrimeiroAcesso(), PDO::PARAM_INT);

            if ($alterarSenha) {
                $stmt->bindValue(":hashSenha", $usuario->getSenha());
            }

            $stmt->bindValue(":idOcupacao", $usuario->getOcupacao()?->getIdOcupacao());
            $stmt->execute();
        } catch (PDOException $e) {
            error_log(sprintf(
                "[UsuarioDAO::atualizar] usuario %s: %s",
                $usuario->getIdUsuario(),
                $e->getMessage()
            ));

            throw new Exception("Erro ao atualizar o usuário com ID igual a {$usuario->getIdUsuario()}: " . $e->getMessage());
        }
    }

    public function atualizarFotoPerfil(int $idUsuario, string $caminhoFoto): void
    {
        try {
            $sql = "UPDATE usuario SET FotoPerfil = :fotoPerfil WHERE IdUsuario = :idUsuario";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":fotoPerfil", $caminhoFoto);
            $stmt->bindValue(":idUsuario", $idUsuario);

            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar a foto de perfil do usuário de ID {$idUsuario}: " . $e->getMessage());
        }
    }

    public function atualizarHashSenha(int $idUsuario, string $hashSenha): void
    {
        try {
            $sql = "UPDATE usuario SET HashSenha = :hashSenha WHERE IdUsuario = :idUsuario";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":hashSenha", $hashSenha);
            $stmt->bindValue(":idUsuario", $idUsuario);

            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar a senha do usuário de ID igual a {$idUsuario}: " . $e->getMessage());
        }
    }

    public function deletar(int $idUsuario): void
    {
        try {
            $sql = "DELETE FROM usuario WHERE IdUsuario = :idUsuario";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idUsuario", $idUsuario);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao deletar o usuario de ID igual a {$idUsuario}: " . $e->getMessage());
        }
    }

    public function verificarSeUsuarioExiste(int $idUsuario): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM usuario WHERE IdUsuario = :idUsuario";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idUsuario", $idUsuario);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar se usuário existe: " . $e->getMessage());
        }
    }

    public function verificarCorreioEletronicoExiste(string $correioEletronico): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM usuario WHERE CorreioEletronico = :correioEletronico";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":correioEletronico", $correioEletronico);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar se e-mail existe: " . $e->getMessage());
        }
    }

    public function verificarNomeAventureiroExiste(string $nomeAventureiro): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM usuario WHERE NomeAventureiro = :nomeAventureiro";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":nomeAventureiro", $nomeAventureiro);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar se nome de aventureiro existe: " . $e->getMessage());
        }
    }

    public function verificarEmailParaOutroUsuario(string $correioEletronico, int $idUsuario): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM usuario WHERE CorreioEletronico = :correioEletronico AND IdUsuario != :idUsuario";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":correioEletronico", $correioEletronico);
            $stmt->bindValue(":idUsuario", $idUsuario);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar e-mail para outro usuário: " . $e->getMessage());
        }
    }

    public function verificarNomeAventureiroParaOutroUsuario(string $nomeAventureiro, int $idUsuario): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM usuario WHERE NomeAventureiro = :nomeAventureiro AND IdUsuario != :idUsuario";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":nomeAventureiro", $nomeAventureiro);
            $stmt->bindValue(":idUsuario", $idUsuario);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar nome de aventureiro para outro usuário: " . $e->getMessage());
        }
    }

    public function verificarSenhaAtual(int $idUsuario, string $senhaAtual): bool
    {
        try {
            $sql = "SELECT HashSenha FROM usuario WHERE IdUsuario = :idUsuario";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idUsuario", $idUsuario);
            $stmt->execute();

            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$registro || empty($registro["HashSenha"])) {
                return false;
            }

            return password_verify($senhaAtual, $registro["HashSenha"]);
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar senha atual do usuário: " . $e->getMessage());
        }
    }

    private function mapearUsuario(array $registro): Usuario
    {
        $ocupacao = new Ocupacao(
            (int) $registro["IdOcupacao"],
            $registro["Titulo"]
        );

        return Usuario::rehidratar(
            (int) $registro["IdUsuario"],
            $registro["Nome"],
            $registro["NomeAventureiro"],
            $registro["CorreioEletronico"],
            $registro["DataNascimento"],
            (bool) $registro["PossuiConhecimento"],
            (bool) $registro["PrimeiroAcesso"],
            (bool) $registro["Admin"],
            $ocupacao,
            $registro["FotoPerfil"] ?? null,
            $registro["HashSenha"] ?? null
        );
    }

    public function alterarPrimeiroAcesso(int $id): void
    {
        try {
            $sql = "UPDATE usuario
                    SET PrimeiroAcesso = 0
                    WHERE IdUsuario = :idUsuario";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idUsuario", $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log(sprintf(
                "[UsuarioDAO::alterarPrimeiroAcesso] usuario %s: %s",
                $id,
                $e->getMessage()
            ));

            throw new Exception("Erro ao atualizar o primeiro acesso do usuário com ID igual a {$id}: " . $e->getMessage());
        }
    }
}