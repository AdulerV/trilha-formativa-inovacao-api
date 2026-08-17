<?php

class ProgressoMissaoAtividadeDTO
{
    public static function toArray(ProgressoMissaoAtividade $progressoMissao): array
    {
        return [
            "usuario" => UsuarioDTO::toArray($progressoMissao->getUsuario()),
            "missao" => MissaoDTO::toArray($progressoMissao->getMissao()),
            "progresso" => $progressoMissao->getProgresso(),
            "tentativasRealizadas" => $progressoMissao->getTentativasRealizadas(),
            "pontuacaoObtida" => $progressoMissao->getPontuacaoObtida()
        ];
    }

    public static function create(array $dados, Usuario $usuario, Missao $missao): ProgressoMissaoAtividade
    {
        return new ProgressoMissaoAtividade(
            $usuario,
            $missao,
            (int) $dados["progresso"],
            (int) $dados["tentativasRealizadas"],
            (float) $dados["pontuacaoObtida"]
        );
    }
}
