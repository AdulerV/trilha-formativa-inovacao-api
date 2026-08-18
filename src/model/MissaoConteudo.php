<?php

declare(strict_types=1);

final class MissaoConteudo extends Missao
{
    private string $url;
    private string $resumo;
    private string $tipoMaterial;

    public const TEXTO = 'texto';
    public const VIDEO = 'video';

    public function __construct(
        ?int $idMissao,
        string $titulo,
        float $pontuacao,
        Tematica $tematica,
        string $url,
        string $resumo,
        string $tipoMaterial
    ) {
        parent::__construct(
            $idMissao,
            $titulo,
            $pontuacao,
            self::TIPO_CONTEUDO,
            $tematica
        );

        $this->setUrl($url);
        $this->setResumo($resumo);
        $this->setTipoMaterial($tipoMaterial);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $url = trim($url);

        if ($url === "" || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new DomainException("URL inválida!");
        }

        $this->url = $url;
        return $this;
    }

    public function getResumo(): string
    {
        return $this->resumo;
    }

    public function setResumo(string $resumo): self
    {
        $resumo = trim($resumo);

        if ($resumo === "") {
            throw new DomainException("O conteúdo precisa de um resumo!");
        }

        $this->resumo = $resumo;
        return $this;
    }

    public function getTipoMaterial(): string
    {
        return $this->tipoMaterial;
    }

    public function setTipoMaterial(string $tipoMaterial): self
    {
        $tipoMaterial = strtolower(trim($tipoMaterial));

        if (!in_array($tipoMaterial, [
            self::TEXTO,
            self::VIDEO
        ])) {
            throw new DomainException("Tipo de conteúdo inválido!");
        }

        $this->tipoMaterial = $tipoMaterial;
        return $this;
    }
}
