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
    public static function create(int $idUsuario, int $idAlternativa, array $dadosExtras = []): AlternativaMarcada
    {
        function criarUsuario(int $idUsuario): Usuario
        {
            return new Usuario(
                $idUsuario,
                "João da Silva",
                "Aventureiro",
                "email@test.com",
                "2000-01-01",
                true,
                true,
                false,
                "Senha@123",
                new Ocupacao(1, "Dev")
            );
        }

        function criarAlternativa(int $idAlternativa): Alternativa
        {
            return new AlternativaMultiplaEscolha(
                $idAlternativa,
                "Texto mock da alternativa selecionada",
                false,
                "verdadeiro_falso"
            );
        }

        $sequencia = isset($dadosExtras['sequenciaRespondida']) ? (int) $dadosExtras['sequenciaRespondida'] : null;
        $associada = isset($dadosExtras['idAlternativaAssociadaRespondida']) ? (int) $dadosExtras['idAlternativaAssociadaRespondida'] : null;

        return new AlternativaMarcada(
            criarUsuario($idUsuario),
            criarAlternativa($idAlternativa),
            false,
            $sequencia,
            $associada
        );
    }
}
