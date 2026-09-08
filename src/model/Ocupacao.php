<?php

declare(strict_types=1);
class Ocupacao
{
    private ?int $idOcupacao = null;
    private string $titulo;

    public function __construct(?int $idOcupacao, string $titulo)
    {
        $this->setIdOcupacao($idOcupacao);
        $this->setTitulo($titulo);
    }

    /**
     * Reconstrói uma ocupação já persistida.
     *
     * setTitulo() normaliza (trim + lowercase) e recusa vazio, o que é
     * adequado na escrita. Na leitura, normalizar de novo mudaria o
     * texto que a tela usa para casar a ocupação do usuário com a
     * lista de opções.
     */
    public static function rehidratar(?int $idOcupacao, string $titulo): self
    {
        $ocupacao = (new ReflectionClass(self::class))->newInstanceWithoutConstructor();

        $ocupacao->idOcupacao = $idOcupacao;
        $ocupacao->titulo = $titulo;

        return $ocupacao;
    }

    public function getIdOcupacao(): ?int
    {
        return $this->idOcupacao;
    }

    public function setIdOcupacao(?int $idOcupacao): self
    {
        if ($this->idOcupacao !== null && $idOcupacao !== $this->idOcupacao) {
            throw new Exception("ID já definido!");
        }

        $this->idOcupacao = $idOcupacao;
        return $this;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $titulo = strtolower(trim($titulo));

        if (empty($titulo)) {
            throw new DomainException("Ocupação inválida!");
        }

        $this->titulo = $titulo;

        return $this;
    }
}
