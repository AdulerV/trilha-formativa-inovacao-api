<?php
class DistintivoAdquiridoDTO
{
    public static function toArray(DistintivoAdquirido $distintivoAdquirido): array
    {
        return [
            "usuario" => UsuarioDTO::toArray($distintivoAdquirido->getUsuario()),
            "distintivo" => DistintivoDTO::toArray($distintivoAdquirido->getDistintivo())
        ];
    }

    /**
     * O vínculo usuário/distintivo é gravado só pelos dois IDs; as
     * entidades completas servem apenas para satisfazer o construtor
     * de DistintivoAdquirido.
     */
    public static function create(int $idUsuario, int $idDistintivo): DistintivoAdquirido
    {
        return new DistintivoAdquirido(
            self::referenciaDeUsuario($idUsuario),
            self::referenciaDeDistintivo($idDistintivo)
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

    private static function referenciaDeDistintivo(int $idDistintivo): Distintivo
    {
        return new Distintivo(
            $idDistintivo,
            "Bronze",
            10,
            "bronze.svg"
        );
    }
}
