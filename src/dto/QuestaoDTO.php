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

    /**
     * Devolve a questão montada para que o controller possa responder
     * com o ID gerado depois de persistir.
     */
    public static function create(array $dados, ?int $id, MissaoAtividade $missao): Questao
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

                /*
                 * "correta" não era repassado aqui, só em
                 * AlternativaDTO::create. Salvar a questão com as
                 * alternativas de múltipla escolha em uma requisição
                 * só falhava sempre com "Faltando o campo 'correta'".
                 */
                if (isset($altDados["correta"])) {
                    $dadosAdicionais["correta"] = (bool) $altDados["correta"];
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

        return $questao;
    }
}
