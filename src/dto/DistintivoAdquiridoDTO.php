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

    public static function create(int $idUsuario, int $idDistintivo): DistintivoAdquirido
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

        function criarDistintivo(int $idDistintivo): Distintivo
        {
            return new Distintivo(
                $idDistintivo,
                "Bronze",
                10,
                "bronze.svg"
            );
        }

        return new DistintivoAdquirido(
            criarUsuario($idUsuario),
            criarDistintivo($idDistintivo)
        );
    }
}
