<?php

declare(strict_types=1);

class QuestaoDTO
{
    public static function toArray(Questao $questao): array
    {
        $alternativas = [];

        foreach ($questao->getAlternativas() as $alternativa) {
            $alternativas[] = AlternativaDTO::toArray($alternativa);
        }

        $array = [
            "id" => $questao->getIdQuestao(),
            "enunciado" => $questao->getEnunciado(),
            "mensagemCorrecao" => $questao->getMensagemCorrecao(),
            "alternativas" => $alternativas
        ];

        if ($questao->getIdMissao() !== null) {
            $array["idMissao"] = $questao->getIdMissao();
        }

        return $array;
    }

    public static function create(array $dados, ?int $id, MissaoAtividade $missao): void
    {
        $questao = $missao->adicionarQuestao(
            $dados["enunciado"],
            $dados["mensagemCorrecao"],
            $id
        );

        if (!empty($dados["alternativas"])) {
            foreach ($dados["alternativas"] as $altDados) {
                $dadosAdicionais = [];

                if (isset($altDados["numeroSequencia"])) {
                    $dadosAdicionais["numeroSequencia"] = (int) $altDados["numeroSequencia"];
                }

                if (isset($altDados["subtipo"])) {
                    $dadosAdicionais["subtipo"] = $altDados["subtipo"];
                }

                if (isset($altDados["alternativaAssociada"])) {
                    $dadosAdicionais["alternativaAssociada"] = $altDados["alternativaAssociada"];
                }

                $questao->adicionarAlternativa(
                    $altDados["texto"],
                    $altDados["tipoAlternativa"],
                    isset($altDados["id"]) ? (int) $altDados["id"] : null,
                    $dadosAdicionais
                );
            }
        }
    }
}
