<?php
class DistintivoDTO
{
    public static function toArray(Distintivo $distintivo): array
    {
        return [
            "id" => $distintivo->getIdDistintivo(),
            "titulo" => $distintivo->getTitulo(),
            "pontuacao" => $distintivo->getPontuacao(),
            "nomeArquivo" => $distintivo->getNomeArquivo()
        ];
    }

    public static function create(array $dados, ?int $id): Distintivo
    {
        return new Distintivo(
            $id,
            $dados["titulo"],
            (float) $dados["pontuacao"],
            $dados["nomeArquivo"]
        );
    }
}
