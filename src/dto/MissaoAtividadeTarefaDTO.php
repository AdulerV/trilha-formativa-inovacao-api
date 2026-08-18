<?php
class MissaoAtividadeTarefaDTO
{
    public static function toArray(MissaoAtividadeTarefa $missao): array
    {
        $questoes = [];

        foreach ($missao->getQuestoes() as $questao) {
            $questoes[] = QuestaoDTO::toArray($questao);
        }

        return [
            "id" => $missao->getIdMissao(),
            "titulo" => $missao->getTitulo(),
            "pontuacao" => $missao->getPontuacao(),
            "tipoAtividade" => $missao->getTipoAtividade(),
            "tematica" => TematicaDTO::toArray($missao->getTematica()),
            "distintivo" => DistintivoDTO::toArray($missao->getDistintivo()),
            "questoes" => $questoes
        ];
    }

    public static function create(array $dados, ?int $id): MissaoAtividadeTarefa
    {
        return new MissaoAtividadeTarefa(
            $id,
            $dados["titulo"],
            (float) $dados["pontuacao"],
            TematicaDTO::create($dados, $dados["idTematica"]),
            $dados["tipoAtividade"],
            new Distintivo($dados["idDistintivo"], "Qualquer", 1, "badgeDefault.svg")
        );
    }
}
