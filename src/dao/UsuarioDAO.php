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
            $sql = "INSERT INTO usuario (Nome, NomeAventureiro, CorreioEletronico, DataNascimento,  PossuiConhecimento, PrimeiroAcesso, HashSenha, IdOcupacao) VALUES (:nome, :nomeAventureiro, :correioEletronico, :dataNascimento, :possuiConhecimento, :primeiroAcesso, :hashSenha, :idOcupacao)";

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
        } catch (PDOException) {
            throw new Exception("Erro ao inserir usuário!");
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

            $usuario = $this->mapearUsuario($registro);

            return $usuario;
        } catch (PDOException) {
            throw new Exception("Erro ao buscar usuário com ID igual a {$idUsuario}");
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
            throw new Exception("Erro ao buscar usuário pelo e-mail: {$correioEletronico}");
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
            ON u.IdOcupacao = o.IdOcupacao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->execute();

            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $usuarios = [];

            foreach ($registros as $registro) {
                $usuarios[] = $this->mapearUsuario($registro);
            }

            return $usuarios;
        } catch (PDOException) {
            throw new Exception("Erro ao listar usuários!");
        }
    }

    /**
     * Atualiza os dados do usuário.
     *
     * A coluna HashSenha só entra no UPDATE quando a entidade carrega
     * uma senha nova. Antes ela era sempre sobrescrita, o que obrigava
     * o frontend a reenviar alguma senha em toda edição — e a solução
     * que ele adotara era mandar a senha ATUAL em claro no campo de
     * nova senha, gerando um hash novo para a mesma senha.
     */
    public function atualizar(Usuario $usuario)
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
            /*
             * O tipo era fixo em PDO::PARAM_NULL, o que faz o PDO
             * gravar NULL qualquer que seja o valor. Como a coluna é
             * NOT NULL, TODA atualização de usuário falhava com
             * "Column 'PossuiConhecimento' cannot be null" e o
             * controller devolvia apenas "Erro interno".
             */
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
            /* A mensagem do banco vai para o log, não para o usuário. */
            error_log(sprintf(
                "[UsuarioDAO::atualizar] usuario %s: %s",
                $usuario->getIdUsuario(),
                $e->getMessage()
            ));

            throw new Exception("Erro ao atualizar o usuário com ID igual a {$usuario->getIdUsuario()}");
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
            throw new Exception("Erro ao atualizar a foto de perfil do usuário de ID {$idUsuario}.");
        }
    }

    /**
     * Atualiza somente o hash da senha.
     *
     * Existe separado de atualizar() de propósito: a redefinição de
     * senha não deve tocar em nome, e-mail, ocupação ou qualquer outro
     * campo do cadastro.
     */
    public function atualizarHashSenha(int $idUsuario, string $hashSenha): void
    {
        try {
            $sql = "UPDATE usuario SET HashSenha = :hashSenha WHERE IdUsuario = :idUsuario";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":hashSenha", $hashSenha);
            $stmt->bindValue(":idUsuario", $idUsuario);

            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao atualizar a senha do usuário de ID igual a {$idUsuario}");
        }
    }

    public function deletar(int $idUsuario)
    {
        try {
            $sql = "DELETE FROM usuario WHERE IdUsuario = :idUsuario";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idUsuario", $idUsuario);
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao deletar o usuario de ID igual a {$idUsuario}");
        }
    }

    public function verificarSeUsuarioExiste(int $idUsuario): bool
    {
        $sql = "SELECT COUNT(*) FROM usuario WHERE IdUsuario = :idUsuario";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":idUsuario", $idUsuario);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function verificarCorreioEletronicoExiste(string $correioEletronico): bool
    {
        $sql = "SELECT COUNT(*) FROM usuario WHERE CorreioEletronico = :correioEletronico";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":correioEletronico", $correioEletronico);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function verificarNomeAventureiroExiste(string $nomeAventureiro): bool
    {
        $sql = "SELECT COUNT(*) FROM usuario WHERE NomeAventureiro = :nomeAventureiro";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":nomeAventureiro", $nomeAventureiro);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function verificarEmailParaOutroUsuario(string $correioEletronico, int $idUsuario): bool
    {
        $sql = "SELECT COUNT(*) FROM usuario WHERE CorreioEletronico = :correioEletronico AND IdUsuario != :idUsuario";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":correioEletronico", $correioEletronico);
        $stmt->bindValue(":idUsuario", $idUsuario);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function verificarNomeAventureiroParaOutroUsuario(string $nomeAventureiro, int $idUsuario): bool
    {
        $sql = "SELECT COUNT(*) FROM usuario WHERE NomeAventureiro = :nomeAventureiro AND IdUsuario != :idUsuario";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":nomeAventureiro", $nomeAventureiro);
        $stmt->bindValue(":idUsuario", $idUsuario);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function verificarSenhaAtual(int $idUsuario, string $senhaAtual): bool
    {
        $sql = "SELECT HashSenha FROM usuario WHERE IdUsuario = :idUsuario";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":idUsuario", $idUsuario);
        $stmt->execute();

        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        return password_verify($senhaAtual, $registro["HashSenha"]);
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
}
