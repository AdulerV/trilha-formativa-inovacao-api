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
            isset($dados["possuiConhecimento"])
                ? (bool) $dados["possuiConhecimento"]
                : null,
            (bool) ($dados["primeiroAcesso"] ?? false),
            false,
            /*
             * Na edição a senha é opcional. Antes o acesso era
             * `$dados["senha"] ?? $dados["novaSenha"]`: sem nenhuma das
             * duas chaves o PHP emitia aviso de índice indefinido e
             * passava null a um parâmetro `string`, virando TypeError —
             * que não é Exception e escapava dos catch do controller,
             * derrubando a requisição com 500 sem corpo.
             */
            self::extrairSenha($dados),
            new Ocupacao((int) $dados["idOcupacao"], "Qualquer")
        );
    }

    /**
     * Senha em claro presente no corpo da requisição, se houver.
     *
     * `senha` é a chave do cadastro; `novaSenha`, a da edição. String
     * vazia ou só espaços conta como ausência: enviar "" nunca deve
     * ser interpretado como uma nova senha.
     */
    private static function extrairSenha(array $dados): ?string
    {
        foreach (["senha", "novaSenha"] as $chave) {
            $valor = $dados[$chave] ?? null;

            if (is_string($valor) && trim($valor) !== "") {
                return $valor;
            }
        }

        return null;
    }
}
