<?php

declare(strict_types=1);

class VerificacaoEmailDAO
{
    private const FORMATO_DATA = "Y-m-d H:i:s";

    private const COLUNAS = "IdVerificacaoEmail,
            CorreioEletronico,
            HashCodigo,
            HashComprovante,
            Tentativas,
            DataCriacao,
            DataExpiracao,
            DataVerificacao,
            DataExpiracaoComprovante,
            DataConsumo,
            EnderecoIp";

    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function salvar(VerificacaoEmail $verificacao): void
    {
        try {
            $sql = "INSERT INTO verificacao_email
            (CorreioEletronico, HashCodigo, Tentativas, DataCriacao, DataExpiracao, EnderecoIp)
            VALUES (:correioEletronico, :hashCodigo, :tentativas, :dataCriacao, :dataExpiracao, :enderecoIp)";

            $stmt = $this->conexao->prepare($sql);

            $stmt->bindValue(":correioEletronico", $verificacao->getCorreioEletronico());
            $stmt->bindValue(":hashCodigo", $verificacao->getHashCodigo());
            $stmt->bindValue(":tentativas", $verificacao->getTentativas(), PDO::PARAM_INT);
            $stmt->bindValue(":dataCriacao", $verificacao->getDataCriacao()->format(self::FORMATO_DATA));
            $stmt->bindValue(":dataExpiracao", $verificacao->getDataExpiracao()->format(self::FORMATO_DATA));
            $stmt->bindValue(":enderecoIp", $verificacao->getEnderecoIp());

            $stmt->execute();

            $verificacao->setIdVerificacaoEmail((int) $this->conexao->lastInsertId());
        } catch (PDOException $e) {
            throw new Exception("Erro ao registrar a verificação de e-mail: " . $e->getMessage());
        }
    }

    /**
     * Recupera a verificação em aberto mais recente de um e-mail.
     *
     * "Em aberto" significa ainda não confirmada e ainda não consumida.
     * Só existe uma por e-mail em condições normais, porque emitir um
     * código novo invalida os anteriores, mas a ordenação protege
     * contra corridas.
     */
    public function buscarPendentePorEmail(string $correioEletronico): ?VerificacaoEmail
    {
        try {
            $sql = "SELECT " . self::COLUNAS . "
            FROM verificacao_email
            WHERE CorreioEletronico = :correioEletronico
            AND DataVerificacao IS NULL
            AND DataConsumo IS NULL
            ORDER BY IdVerificacaoEmail DESC
            LIMIT 1";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":correioEletronico", strtolower(trim($correioEletronico)));
            $stmt->execute();

            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            return $registro ? $this->mapear($registro) : null;
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar a verificação de e-mail: " . $e->getMessage());
        }
    }

    /**
     * Recupera a verificação já confirmada correspondente ao resumo do
     * comprovante apresentado no cadastro.
     */
    public function buscarPorHashComprovante(string $hashComprovante): ?VerificacaoEmail
    {
        try {
            $sql = "SELECT " . self::COLUNAS . "
            FROM verificacao_email
            WHERE HashComprovante = :hashComprovante";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":hashComprovante", $hashComprovante);
            $stmt->execute();

            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            return $registro ? $this->mapear($registro) : null;
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar o comprovante de verificação: " . $e->getMessage());
        }
    }

    /**
     * Contabiliza uma tentativa de código errada.
     *
     * O incremento acontece no banco (Tentativas = Tentativas + 1) e
     * não em PHP, para que tentativas simultâneas não se sobrescrevam —
     * caso contrário o limite seria contornável disparando requisições
     * em paralelo.
     */
    public function registrarTentativa(int $idVerificacaoEmail): void
    {
        try {
            $sql = "UPDATE verificacao_email
            SET Tentativas = Tentativas + 1
            WHERE IdVerificacaoEmail = :id";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":id", $idVerificacaoEmail, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao registrar a tentativa de verificação: " . $e->getMessage());
        }
    }

    /**
     * Marca a verificação como confirmada e grava o comprovante.
     *
     * A cláusula "DataVerificacao IS NULL AND DataConsumo IS NULL"
     * torna a confirmação atômica: duas requisições com o código certo
     * chegando juntas produzem apenas um comprovante.
     *
     * @return bool Verdadeiro quando este chamador confirmou.
     */
    public function marcarComoVerificado(
        int $idVerificacaoEmail,
        string $hashComprovante,
        DateTime $expiracaoComprovante
    ): bool {
        try {
            $sql = "UPDATE verificacao_email
            SET DataVerificacao = :dataVerificacao,
                HashComprovante = :hashComprovante,
                DataExpiracaoComprovante = :dataExpiracaoComprovante
            WHERE IdVerificacaoEmail = :id
            AND DataVerificacao IS NULL
            AND DataConsumo IS NULL";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":dataVerificacao", (new DateTime())->format(self::FORMATO_DATA));
            $stmt->bindValue(":hashComprovante", $hashComprovante);
            $stmt->bindValue(":dataExpiracaoComprovante", $expiracaoComprovante->format(self::FORMATO_DATA));
            $stmt->bindValue(":id", $idVerificacaoEmail, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao confirmar a verificação de e-mail: " . $e->getMessage());
        }
    }

    /**
     * Consome o comprovante, também de forma atômica: garante que um
     * mesmo comprovante não crie duas contas.
     *
     * @return bool Verdadeiro quando este chamador consumiu.
     */
    public function marcarComoConsumido(int $idVerificacaoEmail): bool
    {
        try {
            $sql = "UPDATE verificacao_email
            SET DataConsumo = :dataConsumo
            WHERE IdVerificacaoEmail = :id
            AND DataConsumo IS NULL";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":dataConsumo", (new DateTime())->format(self::FORMATO_DATA));
            $stmt->bindValue(":id", $idVerificacaoEmail, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao consumir o comprovante de verificação: " . $e->getMessage());
        }
    }

    /**
     * Invalida tudo que estiver em aberto para um e-mail. Chamado antes
     * de emitir um código novo, para que o anterior deixe de valer.
     */
    public function invalidarPendentesPorEmail(string $correioEletronico): void
    {
        try {
            $sql = "UPDATE verificacao_email
            SET DataConsumo = :dataConsumo
            WHERE CorreioEletronico = :correioEletronico
            AND DataConsumo IS NULL";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":dataConsumo", (new DateTime())->format(self::FORMATO_DATA));
            $stmt->bindValue(":correioEletronico", strtolower(trim($correioEletronico)));
            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao invalidar as verificações pendentes: " . $e->getMessage());
        }
    }

    public function contarSolicitacoesRecentes(string $correioEletronico, int $minutosDaJanela): int
    {
        try {
            $inicioDaJanela = (new DateTime())->modify("-{$minutosDaJanela} minutes");

            $sql = "SELECT COUNT(*) FROM verificacao_email
            WHERE CorreioEletronico = :correioEletronico
            AND DataCriacao >= :inicioDaJanela";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":correioEletronico", strtolower(trim($correioEletronico)));
            $stmt->bindValue(":inicioDaJanela", $inicioDaJanela->format(self::FORMATO_DATA));
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar as solicitações recentes de verificação: " . $e->getMessage());
        }
    }

    /**
     * Rotina de higiene, pode ser agendada.
     */
    public function limparSolicitacoesAntigas(int $diasDeRetencao = 30): int
    {
        try {
            $limite = (new DateTime())->modify("-{$diasDeRetencao} days");

            $sql = "DELETE FROM verificacao_email WHERE DataCriacao < :limite";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":limite", $limite->format(self::FORMATO_DATA));
            $stmt->execute();

            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Erro ao limpar as verificações antigas: " . $e->getMessage());
        }
    }

    private function mapear(array $registro): VerificacaoEmail
    {
        return new VerificacaoEmail(
            (int) $registro["IdVerificacaoEmail"],
            $registro["CorreioEletronico"],
            $registro["HashCodigo"],
            new DateTime($registro["DataCriacao"]),
            new DateTime($registro["DataExpiracao"]),
            (int) $registro["Tentativas"],
            $registro["HashComprovante"],
            $registro["DataVerificacao"] !== null ? new DateTime($registro["DataVerificacao"]) : null,
            $registro["DataExpiracaoComprovante"] !== null
                ? new DateTime($registro["DataExpiracaoComprovante"])
                : null,
            $registro["DataConsumo"] !== null ? new DateTime($registro["DataConsumo"]) : null,
            $registro["EnderecoIp"]
        );
    }
}
