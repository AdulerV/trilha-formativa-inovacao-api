<?php
class UsuarioDTO
{
    public static function toArray(Usuario $usuario): array
    {
        return [
            "id" => $usuario->getIdUsuario(),
            "nomeUsuario" => $usuario->getNomeUsuario(),
            "nomeAventureiro" => $usuario->getNomeAventureiro(),
            "correioEletronico" => $usuario->getCorreioEletronico(),
            "dataNascimento" => $usuario->getDataNascimento()?->format('Y-m-d'),
            "possuiConhecimento" => $usuario->isPossuiConhecimento(),
            "primeiroAcesso" => $usuario->isPrimeiroAcesso(),
            "fotoPerfil" => $usuario->getFotoPerfil(),
            "ocupacao" => OcupacaoDTO::toArray($usuario->getOcupacao())
        ];
    }

    public static function create(array $dados, ?int $id): Usuario
    {
        return new Usuario(
            $id,
            $dados["nomeUsuario"],
            $dados["nomeAventureiro"],
            $dados["correioEletronico"],
            $dados["dataNascimento"] ?? null,
            (bool) $dados["possuiConhecimento"] ?? false,
            (bool) $dados["primeiroAcesso"],
            false,
            $dados["senha"] ?? $dados["novaSenha"],
            new Ocupacao((int) $dados["idOcupacao"], "Qualquer")
        );
    }
}
