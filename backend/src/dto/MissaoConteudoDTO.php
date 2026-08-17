<?php
class MissaoConteudoDTO
{
    public static function toArray(MissaoConteudo $missao): array
    {
        return [
            "id" => $missao->getIdMissao(),
            "titulo" => $missao->getTitulo(),
            "pontuacao" => $missao->getPontuacao(),
            "url" => $missao->getUrl(),
            "resumo" => $missao->getResumo(),
            "tipoMaterial" => $missao->getTipoMaterial(),
            "tematica" => TematicaDTO::toArray($missao->getTematica())
        ];
    }

    public static function create(array $dados, ?int $id): MissaoConteudo
    {
        return new MissaoConteudo(
            $id,
            $dados["titulo"],
            (float) $dados["pontuacao"],
            TematicaDTO::create($dados, $dados["idTematica"]),
            $dados["url"],
            $dados["resumo"],
            $dados["tipoMaterial"]
        );
    }
}
