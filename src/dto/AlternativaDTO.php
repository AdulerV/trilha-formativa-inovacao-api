<?php

declare(strict_types=1);

class AlternativaDTO
{
    public static function toArray(Alternativa $alternativa): array
    {
        $array = [
            "id" => $alternativa->getIdAlternativa(),
            "texto" => $alternativa->getTexto(),
            "tipoAlternativa" => $alternativa->getTipoAlternativa(),
        ];

        if ($alternativa instanceof AlternativaOrdenacao) {
            $array["numeroSequencia"] = $alternativa->getNumeroSequencia();
        } else if ($alternativa instanceof AlternativaMultiplaEscolha) {
            $array["correta"] = $alternativa->isCorreta();
            $array["subtipo"] = $alternativa->getTipoMultiplaEscolha();
        } elseif ($alternativa instanceof AlternativaAssociacao) {
            $associada = $alternativa->getAlternativaAssociada();
            $array["alternativaAssociada"] = $associada ? [
                "id" => $associada->getIdAlternativa(),
                "texto" => $associada->getTexto(),
                "tipoAlternativa" => $associada->getTipoAlternativa()
            ] : [];
        }

        if ($alternativa->getIdQuestao() !== null) {
            $array["idQuestao"] = $alternativa->getIdQuestao();
        }

        return $array;
    }

    /**
     * Devolve as alternativas criadas, na ordem recebida, para que o
     * controller possa responder com os IDs gerados.
     *
     * @return Alternativa[]
     */
    public static function create(array $dados, ?int $id, Questao $questao): array
    {
        if (isset($dados["texto"])) {
            $dados = [$dados];
        }

        $criadas = [];

        foreach ($dados as $altDados) {
            $dadosAdicionais = [];

            if (isset($altDados["numeroSequencia"])) {
                $dadosAdicionais["numeroSequencia"] = (int) $altDados["numeroSequencia"];
            }

            if (isset($altDados["correta"])) {
                $dadosAdicionais["correta"] = (bool) $altDados["correta"];
            }

            if (isset($altDados["subtipo"])) {
                $dadosAdicionais["subtipo"] = $altDados["subtipo"];
            }

            if (isset($altDados["alternativaAssociada"])) {
                $dadosAdicionais["alternativaAssociada"] = $altDados["alternativaAssociada"];
            }

            $criadas[] = $questao->adicionarAlternativa(
                $altDados["texto"],
                $altDados["tipoAlternativa"],
                isset($altDados["id"]) ? (int) $altDados["id"] : $id,
                $dadosAdicionais
            );
        }

        return $criadas;
    }
}
