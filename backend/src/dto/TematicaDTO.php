<?php
class TematicaDTO
{
    public static function toArray(Tematica $tematica): array
    {
        return [
            "id" => $tematica->getIdTematica(),
            "titulo" => $tematica->getTitulo()
        ];
    }

    public static function create(array $dados, ?int $id): Tematica
    {
        return new Tematica(
            $id,
            $dados["titulo"]
        );
    }
}
