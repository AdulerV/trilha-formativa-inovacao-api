<?php

declare(strict_types=1);

class UploadService
{
    /**
     * Prefixo público das imagens de perfil.
     *
     * É o caminho pelo qual o servidor realmente entrega o arquivo
     * (public/image/upload/perfil/...). O valor gravado no banco
     * passou a ser esse mesmo caminho: antes gravava-se
     * "uploads/perfis/...", que não corresponde a nenhuma URL servida,
     * e o frontend precisava remontar a pasta por conta própria.
     */
    public const PREFIXO_PUBLICO_PERFIL = 'image/upload/perfil/';

    private string $diretorioBase;
    private array $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
    private int $tamanhoMaximoBytes = 2 * 1024 * 1024;

    public function __construct(string $diretorioBase)
    {
        $this->diretorioBase = rtrim(str_replace('\\', '/', $diretorioBase), '/') . '/';
    }

    /**
     * Nome da pasta do usuário, derivado do id e do nome de aventureiro.
     *
     * Centralizado porque o salvamento e a remoção precisam chegar
     * exatamente à mesma pasta.
     */
    public function nomePastaUsuario(int $idUsuario, string $nomeAventureiro): string
    {
        $nomeSanitizado = preg_replace(
            '/[^a-zA-Z0-9_-]/',
            '',
            str_replace(' ', '_', strtolower($nomeAventureiro))
        );

        return "{$idUsuario}_{$nomeSanitizado}/";
    }

    /**
     * Remove a imagem de perfil do usuário do disco.
     *
     * Apaga o conteúdo da pasta do usuário e, se ela ficar vazia, a
     * própria pasta. Um arquivo que já não existe não é tratado como
     * erro: o objetivo é o estado final "sem imagem".
     */
    public function removerImagemPerfil(
        int $idUsuario,
        string $nomeAventureiro,
        ?string $caminhoRegistrado = null
    ): void {
        $pastas = [$this->nomePastaUsuario($idUsuario, $nomeAventureiro)];

        /*
         * Registros antigos podem apontar para uma pasta montada com
         * outro nome de aventureiro (o usuário pode tê-lo trocado
         * depois do upload). O caminho gravado no banco é a referência
         * mais confiável do que está no disco.
         */
        $pastaRegistrada = $this->extrairPastaDoCaminho($caminhoRegistrado);

        if ($pastaRegistrada !== null && !in_array($pastaRegistrada, $pastas, true)) {
            $pastas[] = $pastaRegistrada;
        }

        foreach ($pastas as $pasta) {
            $caminhoAbsoluto = $this->diretorioBase . $pasta;

            if (!is_dir($caminhoAbsoluto)) {
                continue;
            }

            foreach (glob($caminhoAbsoluto . '*') ?: [] as $arquivo) {
                if (is_file($arquivo)) {
                    unlink($arquivo);
                }
            }

            @rmdir($caminhoAbsoluto);
        }
    }

    /**
     * Última pasta de um caminho registrado, sem o nome do arquivo.
     * Evita sair do diretório base por caminhos com "..".
     */
    private function extrairPastaDoCaminho(?string $caminho): ?string
    {
        if ($caminho === null || trim($caminho) === "") {
            return null;
        }

        $partes = explode('/', str_replace('\\', '/', trim($caminho)));

        array_pop($partes);

        $pasta = end($partes);

        if ($pasta === false || $pasta === "" || !preg_match('/^\d+_[a-zA-Z0-9_-]*$/', $pasta)) {
            return null;
        }

        return $pasta . '/';
    }

    public function salvarImagemPerfil(array $arquivo, int $idUsuario, string $nomeAventureiro): string
    {
        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erro ao receber o arquivo enviado.");
        }

        if ($arquivo['size'] > $this->tamanhoMaximoBytes) {
            throw new DomainException("A imagem deve ter no máximo 2MB.");
        }

        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, $this->extensoesPermitidas)) {
            throw new DomainException("Formato inválido. Apenas JPG, PNG e WEBP são permitidos.");
        }

        $nomePastaUsuario = $this->nomePastaUsuario($idUsuario, $nomeAventureiro);

        $caminhoPastaAbsoluto = $this->diretorioBase . $nomePastaUsuario;

        if (!is_dir($caminhoPastaAbsoluto)) {
            mkdir($caminhoPastaAbsoluto, 0755, true);
        } else {
            $arquivosAntigos = glob($caminhoPastaAbsoluto . '*');
            foreach ($arquivosAntigos as $arquivoAntigo) {
                if (is_file($arquivoAntigo)) {
                    unlink($arquivoAntigo);
                }
            }
        }

        $nomeUnico = bin2hex(random_bytes(8)) . '.' . $extensao;
        $caminhoCompleto = $caminhoPastaAbsoluto . $nomeUnico;

        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
            throw new Exception("Falha ao salvar a imagem no servidor.");
        }

        return self::PREFIXO_PUBLICO_PERFIL . $nomePastaUsuario . $nomeUnico;
    }
}