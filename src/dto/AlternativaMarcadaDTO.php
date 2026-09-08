<?php

declare(strict_types=1);

class AlternativaMarcadaDTO
{
    public static function toArray(AlternativaMarcada $alternativaMarcada): array
    {
        $array = [
            "usuario" => UsuarioDTO::toArray($alternativaMarcada->getUsuario()),
            "alternativa" => AlternativaDTO::toArray($alternativaMarcada->getAlternativa()),
            "correta" => $alternativaMarcada->isCorreta()
        ];

        if ($alternativaMarcada->getIdAlternativaAssociadaRespondida() !== null) {
            $array["idAlternativaAssociadaRespondida"] = $alternativaMarcada->getIdAlternativaAssociadaRespondida();
        }

        if ($alternativaMarcada->getSequenciaRespondida() !== null) {
            $array["sequenciaRespondida"] = $alternativaMarcada->getSequenciaRespondida();
        }

        return $array;
    }
    /**
     * Constrói a marcação a partir dos IDs recebidos.
     *
     * criarUsuario() e criarAlternativa() eram declaradas DENTRO deste
     * método. Em PHP, função declarada dentro de função vai para o
     * escopo GLOBAL na primeira execução: a segunda chamada dentro da
     * mesma requisição derruba o processo com "Cannot redeclare
     * criarUsuario()" — e o mesmo nome existia em DistintivoAdquiridoDTO.
     * Salvar mais de uma resposta em uma única requisição era fatal.
     *
     * As entidades aqui são apenas portadoras dos IDs: o gabarito real
     * é lido do banco em AlternativaMarcadaService::salvar(). Por isso
     * a rehidratação, que não gasta bcrypt em uma senha inventada.
     */
    public static function create(int $idUsuario, int $idAlternativa, array $dadosExtras = []): AlternativaMarcada
    {
        $sequencia = isset($dadosExtras['sequenciaRespondida'])
            ? (int) $dadosExtras['sequenciaRespondida']
            : null;

        $associada = isset($dadosExtras['idAlternativaAssociadaRespondida'])
            ? (int) $dadosExtras['idAlternativaAssociadaRespondida']
            : null;

        return new AlternativaMarcada(
            self::referenciaDeUsuario($idUsuario),
            self::referenciaDeAlternativa($idAlternativa),
            false,
            $sequencia,
            $associada
        );
    }

    private static function referenciaDeUsuario(int $idUsuario): Usuario
    {
        return Usuario::rehidratar(
            $idUsuario,
            "",
            "",
            "",
            null,
            null,
            false,
            false,
            Ocupacao::rehidratar(null, "")
        );
    }

    private static function referenciaDeAlternativa(int $idAlternativa): Alternativa
    {
        return new AlternativaMultiplaEscolha(
            $idAlternativa,
            "Alternativa referenciada pela marcação",
            false,
            "verdadeiro_falso"
        );
    }
}
