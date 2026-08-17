<?php
class MissaoAtividadeDTO
{
    public static function toArray(MissaoAtividade $missao): array
    {
        if (
            $missao->getTipoAtividade() === MissaoAtividade::TAREFA
            || $missao->getTipoAtividade() === MissaoAtividade::TAREFA_FINAL
        ) {
            return MissaoAtividadeTarefaDTO::toArray($missao);
        }

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
            "questoes" => $questoes
        ];
    }
    public static function create(array $dados, ?int $id): MissaoAtividade
    {
        if (
            $dados["tipoAtividade"] === MissaoAtividade::TAREFA
            ||
            $dados["tipoAtividade"] === MissaoAtividade::TAREFA_FINAL
        ) {
            return MissaoAtividadeTarefaDTO::create($dados, $id);
        }

        return new MissaoAtividade(
            $id,
            $dados["titulo"],
            (float) $dados["pontuacao"],
            TematicaDTO::create($dados, $dados["idTematica"]),
            $dados["tipoAtividade"]
        );
    }
}
