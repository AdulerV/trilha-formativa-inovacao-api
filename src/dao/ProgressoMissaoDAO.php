<?php

declare(strict_types=1);

class ProgressoMissaoDAO
{
    private PDO $conexao;
    private ProgressoMissaoAtividadeDAO $progressoAtividadeDAO;

    public function __construct(
        PDO $conexao,
        ProgressoMissaoAtividadeDAO $progressoAtividadeDAO
    ) {
        $this->conexao = $conexao;
        $this->progressoAtividadeDAO = $progressoAtividadeDAO;
    }

    public function salvar(ProgressoMissao $progresso): void
    {
        try {
            $this->conexao->beginTransaction();

            $this->inserirProgressoBase($progresso);
            $this->salvarEspecializacao($progresso);

            $this->conexao->commit();
        } catch (PDOException) {
            $this->conexao->rollBack();
            throw new Exception("Erro ao salvar progresso!");
        }
    }

    private function inserirProgressoBase(ProgressoMissao $progresso): void
    {
        try {
            $sql = "INSERT INTO progresso_missao (IdUsuario, IdMissao, Progresso)
                    VALUES (:idUsuario, :idMissao, :progresso)";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idUsuario", $progresso->getUsuario()->getIdUsuario());
            $stmt->bindValue(":idMissao", $progresso->getMissao()->getIdMissao());
            $stmt->bindValue(":progresso", $progresso->getProgresso());
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao salvar progresso base!");
        }
    }

    private function salvarEspecializacao(ProgressoMissao $progresso): void
    {
        try {
            if ($progresso instanceof ProgressoMissaoAtividade) {
                $this->progressoAtividadeDAO->salvar($progresso);
            }
        } catch (PDOException) {
            throw new Exception("Erro ao salvar especialização do progresso base!");
        }
    }

    public function buscarPorId(int $idUsuario, int $idMissao): ProgressoMissao
    {
        $sql = $this->getSqlBase() . " WHERE p.IdUsuario = :idUsuario AND p.IdMissao = :idMissao";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":idUsuario", $idUsuario);
        $stmt->bindValue(":idMissao", $idMissao);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dados) {
            throw new Exception("Progresso de missão não encontrado.");
        }

        return $this->processarRegistroProgresso($dados);
    }

    public function listar(): array
    {
        $sql = $this->getSqlBase();
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute();

        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $progressoLista = [];

        foreach ($dados as $linha) {
            $progressoLista[] = $this->processarRegistroProgresso($linha);
        }

        return $progressoLista;
    }

    public function listarPorUsuario(int $idUsuario): array
    {
        $sql = $this->getSqlBase() . " WHERE p.IdUsuario = :idUsuario";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":idUsuario", $idUsuario);
        $stmt->execute();

        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $progressoLista = [];

        foreach ($dados as $linha) {
            $progressoLista[] = $this->processarRegistroProgresso($linha);
        }

        return $progressoLista;
    }

    public function atualizar(ProgressoMissao $progresso): void
    {
        try {
            $this->conexao->beginTransaction();

            $sql = "UPDATE progresso_missao 
                    SET Progresso = :progresso
                    WHERE IdUsuario = :idUsuario AND IdMissao = :idMissao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":progresso", $progresso->getProgresso());
            $stmt->bindValue(":idUsuario", $progresso->getUsuario()->getIdUsuario());
            $stmt->bindValue(":idMissao", $progresso->getMissao()->getIdMissao());
            $stmt->execute();

            if ($progresso instanceof ProgressoMissaoAtividade) {
                $this->progressoAtividadeDAO->atualizar($progresso);
            }

            $this->conexao->commit();
        } catch (PDOException) {
            $this->conexao->rollBack();
            throw new Exception("Erro ao atualizar progresso!");
        }
    }

    public function deletar(int $idUsuario, int $idMissao): void
    {
        try {
            $sql = "DELETE FROM progresso_missao 
                    WHERE IdUsuario = :idUsuario AND IdMissao = :idMissao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idUsuario", $idUsuario);
            $stmt->bindValue(":idMissao", $idMissao);
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao deletar progresso!");
        }
    }

    public function verificarSeProgressoMissaoExiste(int $idUsuario, int $idMissao): bool
    {
        $sql = "SELECT COUNT(*) FROM progresso_missao 
                WHERE IdUsuario = :idUsuario AND IdMissao = :idMissao";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(':idUsuario', $idUsuario);
        $stmt->bindValue(':idMissao', $idMissao);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    private function getSqlBase(): string
    {
        return "SELECT 
        o.IdOcupacao, o.Titulo AS TituloOcupacao, 
        u.IdUsuario, u.Nome, u.NomeAventureiro, u.CorreioEletronico, u.DataNascimento, u.PossuiConhecimento, u.PrimeiroAcesso, u.Admin, u.FotoPerfil,
        t.IdTematica, t.Titulo AS TituloTematica, 
        m.IdMissao, m.Titulo AS TituloMissao, m.Pontuacao, m.TipoMissao,
        mc.URL, mc.Resumo, mc.TipoMaterial,
        ma.TipoAtividade,
        d.IdDistintivo, d.Titulo AS TituloDistintivo, d.Pontuacao AS PontuacaoDistintivo, d.NomeArquivo AS NomeArquivoDistintivo,
        p.Progresso,
        pa.TentativasRealizadas, pa.PontuacaoObtida
    FROM progresso_missao p
    LEFT JOIN progresso_missao_atividade pa 
        ON p.IdUsuario = pa.IdUsuario AND p.IdMissao = pa.IdMissao
    INNER JOIN usuario u ON p.IdUsuario = u.IdUsuario
    INNER JOIN ocupacao o ON u.IdOcupacao = o.IdOcupacao
    INNER JOIN missao m ON p.IdMissao = m.IdMissao
    LEFT JOIN missao_conteudo mc ON m.IdMissao = mc.IdMissao
    LEFT JOIN missao_atividade ma ON m.IdMissao = ma.IdMissao
    LEFT JOIN missao_atividade_tarefa mat ON ma.IdMissao = mat.IdMissao
    LEFT JOIN distintivo d ON mat.IdDistintivo = d.IdDistintivo
    INNER JOIN tematica t ON m.IdTematica = t.IdTematica";
    }

    /**
     * Monta o progresso a partir da linha já trazida pelo JOIN.
     *
     * Antes, a variante "atividade" era delegada a um mapeador que
     * disparava MissaoAtividadeDAO::buscarPorId() para CADA linha —
     * e esse método carrega questões e alternativas da missão, dados
     * que nenhuma tela consome no progresso. Era um N+1 completo em
     * cima de uma consulta que já traz tudo o que o DTO precisa.
     */
    private function processarRegistroProgresso(array $dados): ProgressoMissao
    {
        $usuario = $this->mapearUsuario($dados);

        $tematica = new Tematica(
            (int) $dados["IdTematica"],
            $dados["TituloTematica"]
        );

        $missao = $this->mapearMissao($dados, $tematica);

        $ehAtividade = !is_null($dados["TentativasRealizadas"])
            && !is_null($dados["PontuacaoObtida"]);

        if ($ehAtividade) {
            return ProgressoMissaoAtividade::rehidratarAtividade(
                $usuario,
                $missao,
                (int) $dados["Progresso"],
                (int) $dados["TentativasRealizadas"],
                (float) $dados["PontuacaoObtida"]
            );
        }

        return ProgressoMissao::rehidratar(
            $usuario,
            $missao,
            (int) $dados["Progresso"]
        );
    }

    private function mapearMissao(array $dados, Tematica $tematica): Missao
    {
        $idMissao = (int) $dados["IdMissao"];
        $titulo = $dados["TituloMissao"];
        $pontuacao = (float) $dados["Pontuacao"];
        $tipoMissao = $dados["TipoMissao"] ?? null;

        if ($tipoMissao === Missao::TIPO_CONTEUDO || !empty($dados["TipoMaterial"])) {
            return new MissaoConteudo(
                $idMissao,
                $titulo,
                $pontuacao,
                $tematica,
                $dados["URL"] ?? "",
                $dados["Resumo"] ?? "",
                $dados["TipoMaterial"] ?? MissaoConteudo::VIDEO
            );
        }

        $tipoAtividade = $dados["TipoAtividade"] ?? MissaoAtividade::QUIZ;

        if (
            ($tipoAtividade === MissaoAtividade::TAREFA || $tipoAtividade === MissaoAtividade::TAREFA_FINAL) &&
            !empty($dados["IdDistintivo"])
        ) {
            $distintivo = new Distintivo(
                (int) $dados["IdDistintivo"],
                $dados["TituloDistintivo"] ?? "",
                (float) ($dados["PontuacaoDistintivo"] ?? 0.0),
                $dados["NomeArquivoDistintivo"] ?? ""
            );

            return new MissaoAtividadeTarefa(
                $idMissao,
                $titulo,
                $pontuacao,
                $tematica,
                $tipoAtividade,
                $distintivo
            );
        }

        return new MissaoAtividade(
            $idMissao,
            $titulo,
            $pontuacao,
            $tematica,
            $tipoAtividade
        );
    }

    private function mapearUsuario(array $dados): Usuario
    {
        $ocupacao = new Ocupacao(
            (int) $dados["IdOcupacao"],
            $dados["TituloOcupacao"]
        );

        return Usuario::rehidratar(
            (int) $dados["IdUsuario"],
            $dados["Nome"],
            $dados["NomeAventureiro"],
            $dados["CorreioEletronico"],
            $dados["DataNascimento"],
            (bool) $dados["PossuiConhecimento"],
            (bool) $dados["PrimeiroAcesso"],
            (bool) $dados["Admin"],
            $ocupacao,
            $dados["FotoPerfil"] ?? null
        );
    }
}
