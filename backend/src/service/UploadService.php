<?php

declare(strict_types=1);

class UploadService
{
    private string $diretorioBase;
    private array $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
    private int $tamanhoMaximoBytes = 2 * 1024 * 1024;

    public function __construct(string $diretorioBase)
    {
        $this->diretorioBase = rtrim(str_replace('\\', '/', $diretorioBase), '/') . '/';
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

        $nomeSanitizado = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '_', strtolower($nomeAventureiro)));
        
        $nomePastaUsuario = "{$idUsuario}_{$nomeSanitizado}/"; 
        
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

        return "uploads/perfis/" . $nomePastaUsuario . $nomeUnico;
    }
}