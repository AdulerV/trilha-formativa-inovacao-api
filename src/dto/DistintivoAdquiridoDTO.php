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
     *
     * Antes, criarUsuario() e criarDistintivo() eram declaradas DENTRO
     * deste método. Função declarada dentro de função em PHP entra no
     * escopo GLOBAL na primeira execução — na segunda chamada o
     * processo morre com "Cannot redeclare criarUsuario()", e o mesmo
     * nome era usado em AlternativaMarcadaDTO. Agora são métodos
     * estáticos privados, sem esse efeito colateral.
     *
     * Também não se usa mais o construtor validante: ele obrigava a
     * inventar nome, e-mail e senha, e cada senha inventada custava
     * ~180 ms de bcrypt por chamada.
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
