<?php
class MissaoDTO
{
    public static function toArray(Missao $missao): array
    {
        if ($missao->getTipoMissao() === Missao::TIPO_CONTEUDO) {
            return MissaoConteudoDTO::toArray($missao);
        }

        return MissaoAtividadeDTO::toArray($missao);
    }

    public static function create(array $dados, ?int $id): Missao
    {
        if ($dados["tipoMissao"] === Missao::TIPO_CONTEUDO) {
            return MissaoConteudoDTO::create($dados, $id);
        }

        return MissaoAtividadeDTO::create($dados, $id);
    }
}
