<?php

declare(strict_types=1);

/**
 * Escrita da especialização de progresso do tipo atividade.
 *
 * A leitura fica em ProgressoMissaoDAO, que já recebe TentativasRealizadas
 * e PontuacaoObtida no mesmo JOIN — não há motivo para consultar a missão
 * novamente linha a linha.
 */
class ProgressoMissaoAtividadeDAO
{
    private PDO $conexao;

    /**
     * $atividadeDAO permanece na assinatura para não quebrar a
     * montagem das rotas; a leitura não depende mais dele.
     */
    public function __construct(PDO $conexao, ?MissaoAtividadeDAO $atividadeDAO = null)
    {
        $this->conexao = $conexao;
    }

    public function salvar(ProgressoMissaoAtividade $progresso): void
    {
        try {
            $sql = "INSERT INTO progresso_missao_atividade 
            (IdUsuario, IdMissao, TentativasRealizadas, PontuacaoObtida)
            VALUES (:idUsuario, :idMissao, :tentativasRealizadas, :pontuacaoObtida)";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idUsuario", $progresso->getUsuario()->getIdUsuario());
            $stmt->bindValue(":idMissao", $progresso->getMissao()->getIdMissao());
            $stmt->bindValue(":tentativasRealizadas", $progresso->getTentativasRealizadas());
            $stmt->bindValue(":pontuacaoObtida", $progresso->getPontuacaoObtida());
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao salvar progresso de missão do tipo atividade!");
        }
    }

    public function atualizar(ProgressoMissaoAtividade $progresso): void
    {
        try {
            $sql = "UPDATE progresso_missao_atividade
            SET TentativasRealizadas = :tentativas,
                PontuacaoObtida = :pontuacao
            WHERE IdUsuario = :idUsuario AND IdMissao = :idMissao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":tentativas", $progresso->getTentativasRealizadas());
            $stmt->bindValue(":pontuacao", $progresso->getPontuacaoObtida());
            $stmt->bindValue(":idUsuario", $progresso->getUsuario()->getIdUsuario());
            $stmt->bindValue(":idMissao", $progresso->getMissao()->getIdMissao());
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao atualizar progresso de missão do tipo atividade!");
        }
    }

}
