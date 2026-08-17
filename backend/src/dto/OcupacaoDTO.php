<?php
class OcupacaoDTO
{
    public static function toArray(Ocupacao $ocupacao): array
    {
        return [
            "id" => $ocupacao->getIdOcupacao(),
            "titulo" => $ocupacao->getTitulo()
        ];
    }

    public static function create(array $dados, ?int $id): Ocupacao
    {
        return new Ocupacao(
            $id,
            $dados["titulo"]
        );
    }
}
