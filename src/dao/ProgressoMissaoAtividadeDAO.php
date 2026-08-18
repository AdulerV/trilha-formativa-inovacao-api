<?php

declare(strict_types=1);

class ProgressoMissaoAtividadeDAO
{
    private PDO $conexao;
    private MissaoAtividadeDAO $atividadeDAO;

    public function __construct(PDO $conexao, MissaoAtividadeDAO $atividadeDAO)
    {
        $this->conexao = $conexao;
        $this->atividadeDAO = $atividadeDAO;
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

    public function mapearProgressoMissaoAtividade(array $dados): ProgressoMissaoAtividade
    {
        $ocupacao = new Ocupacao(
            (int) $dados["IdOcupacao"],
            $dados["TituloOcupacao"]
        );

        $usuario = new Usuario(
            (int) $dados["IdUsuario"],
            $dados["Nome"],
            $dados["NomeAventureiro"],
            $dados["CorreioEletronico"],
            $dados["DataNascimento"],
            (bool) $dados["PossuiConhecimento"],
            (bool) $dados["PrimeiroAcesso"],
            (bool) $dados["Admin"],
            "Senha@123",
            $ocupacao
        );

        $missao = $this->atividadeDAO->buscarPorId(
            (int) $dados["IdMissao"]
        );

        $progresso = new ProgressoMissaoAtividade(
            $usuario,
            $missao,
            (int) $dados["Progresso"],
            (int) $dados["TentativasRealizadas"],
            (float) $dados["PontuacaoObtida"]
        );

        return $progresso;
    }
}
