<?php

declare(strict_types=1);
class MissaoConteudoDAO
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function salvar(int $idMissao, MissaoConteudo $missao): void
    {
        try {
            $sql = "INSERT INTO missao_conteudo (IdMissao, URL, Resumo, TipoMaterial)
                    VALUES (:idMissao, :url, :resumo, :tipoMaterial)";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idMissao", $idMissao);
            $stmt->bindValue(":url", $missao->getUrl());
            $stmt->bindValue(":resumo", $missao->getResumo());
            $stmt->bindValue(":tipoMaterial", $missao->getTipoMaterial());
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao salvar missão do tipo conteúdo!");
        }
    }

    public function listar(): array
    {
        try {
            $sql = "SELECT 
                        m.IdMissao,
                        m.Titulo,
                        m.Pontuacao,
                        mc.URL,
                        mc.Resumo,
                        mc.TipoMaterial,
                        t.IdTematica,
                        t.Titulo AS TituloTematica
                    FROM missao_conteudo AS mc
                    INNER JOIN missao AS m ON m.IdMissao = mc.IdMissao
                    INNER JOIN tematica AS t ON t.IdTematica = m.IdTematica";

            $stmt = $this->conexao->prepare($sql);
            $stmt->execute();

            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $missoes = [];

            foreach ($registros as $registro) {
                $missoes[] = $this->mapearMissaoConteudo($registro);
            }

            return $missoes;
        } catch (PDOException) {
            throw new Exception("Erro ao listar missões do tipo conteúdo!");
        }
    }

    public function buscarPorId(int $idMissao): MissaoConteudo
    {
        try {
            $sql = "SELECT 
                        m.IdMissao,
                        m.Titulo,
                        m.Pontuacao,
                        mc.URL,
                        mc.Resumo,
                        mc.TipoMaterial,
                        t.IdTematica,
                        t.Titulo AS TituloTematica
                    FROM missao_conteudo AS mc
                    INNER JOIN missao AS m ON m.IdMissao = mc.IdMissao
                    INNER JOIN tematica AS t ON t.IdTematica = m.IdTematica
                    WHERE mc.IdMissao = :idMissao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":idMissao", $idMissao);
            $stmt->execute();

            $registro = $stmt->fetch(PDO::FETCH_ASSOC);
            $missao = $this->mapearMissaoConteudo($registro);

            return $missao;
        } catch (PDOException) {
            throw new Exception("Erro ao encontrar missão do tipo conteúdo especificada!");
        }
    }

    public function atualizar(MissaoConteudo $missao): void
    {
        try {
            $sql = "UPDATE missao_conteudo 
                    SET URL = :url, Resumo = :resumo, TipoMaterial = :tipoMaterial
                    WHERE IdMissao = :idMissao";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":url", $missao->getUrl());
            $stmt->bindValue(":resumo", $missao->getResumo());
            $stmt->bindValue(":tipoMaterial", $missao->getTipoMaterial());
            $stmt->bindValue(":idMissao", $missao->getIdMissao());
            $stmt->execute();
        } catch (PDOException) {
            throw new Exception("Erro ao atualizar missão do tipo conteúdo!");
        }
    }

    private function mapearMissaoConteudo(array $dados): MissaoConteudo
    {
        $tematica = new Tematica(
            (int) $dados["IdTematica"],
            $dados["TituloTematica"]
        );

        return new MissaoConteudo(
            (int) $dados["IdMissao"],
            $dados["Titulo"],
            (float) $dados["Pontuacao"],
            $tematica,
            $dados["URL"],
            $dados["Resumo"],
            $dados["TipoMaterial"]
        );
    }
}
