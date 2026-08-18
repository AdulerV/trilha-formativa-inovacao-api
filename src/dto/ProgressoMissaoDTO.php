<?php

class ProgressoMissaoDTO
{
    public static function toArray(ProgressoMissao $progressoMissao): array
    {
        if ($progressoMissao instanceof ProgressoMissaoAtividade) {
            return ProgressoMissaoAtividadeDTO::toArray($progressoMissao);
        }

        
        return [
            "usuario" => UsuarioDTO::toArray($progressoMissao->getUsuario()),
            "missao" => MissaoDTO::toArray($progressoMissao->getMissao()),
            "progresso" => $progressoMissao->getProgresso()
        ];
    }

    public static function create(
        array $dados,
        Usuario $usuario,
        Missao $missao
    ): ProgressoMissao {
        if (
            isset($dados["tentativasRealizadas"]) &&
            isset($dados["pontuacaoObtida"]) &&
            $missao->getTipoMissao() !== "conteudo"
        ) {
            return ProgressoMissaoAtividadeDTO::create($dados, $usuario, $missao);
        }

        return new ProgressoMissao(
            $usuario,
            $missao,
            (int) $dados["progresso"]
        );
    }
}
