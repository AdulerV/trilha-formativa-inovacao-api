<?php

declare(strict_types=1);

class RecuperacaoSenhaDAO
{
    private const FORMATO_DATA = "Y-m-d H:i:s";

    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function salvar(RecuperacaoSenha $recuperacaoSenha): void
    {
        try {
            $sql = "INSERT INTO recuperacao_senha (IdUsuario, HashToken, DataCriacao, DataExpiracao, EnderecoIp)
            VALUES (:idUsuario, :hashToken, :dataCriacao, :dataExpiracao, :enderecoIp)";

            $stmt = $this->conexao->prepare($sql);

            $stmt->bindValue(":idUsuario", $recuperacaoSenha->getIdUsuario());
            $stmt->bindValue(":hashToken", $recuperacaoSenha->getHashToken());
            $stmt->bindValue(":dataCriacao", $recuperacaoSenha->getDataCriacao()->format(self::FORMATO_DATA));
            $stmt->bindValue(":dataExpiracao", $recuperacaoSenha->getDataExpiracao()->format(self::FORMATO_DATA));
            $stmt->bindValue(":enderecoIp", $recuperacaoSenha->getEnderecoIp());

            $stmt->execute();

            $recuperacaoSenha->setIdRecuperacaoSenha((int) $this->conexao->lastInsertId());
        } catch (PDOException) {
            throw new Exception("Erro ao registrar a solicitação de recuperação de senha!");
        }
    }

    /**
     * Localiza a solicitação pelo resumo do token apresentado.
     *
     * A consulta é feita pelo hash — o token em claro nunca chega ao
     * banco de dados.
     */
    public function buscarPorHashToken(string $hashToken): ?RecuperacaoSenha
    {
        try {
            $sql = "SELECT
            IdRecuperacaoSenha,
            IdUsuario,
            HashToken,
            DataCriacao,
            DataExpiracao,
            DataUtilizacao,
            EnderecoIp
            FROM recuperacao_senha
            WHERE HashToken = :hashToken";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":hashToken", $hashToken);
            $stmt->execute();

            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$registro) {
                return null;
            }

            return $this->mapearRecuperacaoSenha($registro);
        } catch (PDOException) {
            throw new Exception("Erro ao buscar a solicitação de recuperação de senha!");
        }
    }

    /**
     * Marca o token como consumido, garantindo o uso único.
     *
     * A cláusula "DataUtilizacao IS NULL" transforma a operação em um
     * consumo atômico: duas requisições simultâneas com o mesmo token
     * não conseguem redefinir a senha duas vezes.
     *
     * @return bool Verdadeiro quando este chamador consumiu o token.
     */
    public function marcarComoUtilizado(int $idRecuperacaoSenha): bool
    {
        try {
            $sql = "UPDATE recuperacao_senha
            SET DataUtilizacao = :dataUtilizacao
            WHERE IdRecuperacaoSenha = :idRecuperacaoSenha
            AND DataUtilizacao IS NULL";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":dataUtilizacao", (new DateTime())->format(self::FORMATO_DATA));
            $stmt->bindValue(":idRecuperacaoSenha", $idRecuperacaoSenha);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException) {
            throw new Exception("Erro ao consumir o token de recuperação de senha!");
        }
    }

    /**
     * Invalida todos os tokens pendentes de um usuário.
     *
     * Chamado tanto na emissão de um novo token quanto após a
     * redefinição bem-sucedida da senha.
     */
    public function invalidarTokensDoUsuario(int $idUsuario): void
    {
        try {
            $sql = "UPDATE recuperacao_senha
            SET DataUtilizacao = :dataUtilizacao
            WHERE IdUsuario = :idUsuario
            AND DataUtilizacao IS NULL";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":dataUtilizacao", (new DateTime())->format(self::FORMATO_DATA));
            $stmt->bindValue(":idUsuario", $idUsuario);
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao invalidar os tokens de recuperação de senha!");
        }
    }

    /**
     * Conta as solicitações feitas por um usuário dentro da janela
     * informada. Base do rate limiting por conta.
     */
    public function contarSolicitacoesRecentes(int $idUsuario, int $minutosDaJanela): int
    {
        try {
            $inicioDaJanela = (new DateTime())->modify("-{$minutosDaJanela} minutes");

            $sql = "SELECT COUNT(*) FROM recuperacao_senha
            WHERE IdUsuario = :idUsuario
            AND DataCriacao >= :inicioDaJanela";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idUsuario", $idUsuario);
            $stmt->bindValue(":inicioDaJanela", $inicioDaJanela->format(self::FORMATO_DATA));
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException) {
            throw new Exception("Erro ao verificar as solicitações recentes de recuperação de senha!");
        }
    }

    /**
     * Remove solicitações expiradas ou já utilizadas há mais de N dias.
     * Rotina de higiene, pode ser agendada.
     */
    public function limparSolicitacoesAntigas(int $diasDeRetencao = 30): int
    {
        try {
            $limite = (new DateTime())->modify("-{$diasDeRetencao} days");

            $sql = "DELETE FROM recuperacao_senha WHERE DataCriacao < :limite";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":limite", $limite->format(self::FORMATO_DATA));
            $stmt->execute();

            return $stmt->rowCount();
        } catch (PDOException) {
            throw new Exception("Erro ao limpar as solicitações antigas de recuperação de senha!");
        }
    }

    private function mapearRecuperacaoSenha(array $registro): RecuperacaoSenha
    {
        return new RecuperacaoSenha(
            (int) $registro["IdRecuperacaoSenha"],
            (int) $registro["IdUsuario"],
            $registro["HashToken"],
            new DateTime($registro["DataCriacao"]),
            new DateTime($registro["DataExpiracao"]),
            $registro["DataUtilizacao"] !== null ? new DateTime($registro["DataUtilizacao"]) : null,
            $registro["EnderecoIp"]
        );
    }
}
