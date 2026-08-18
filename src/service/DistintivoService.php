<?php
class DistintivoService
{
    private DistintivoDAO $distintivoDAO;

    public function __construct(DistintivoDAO $distintivoDAO)
    {
        $this->distintivoDAO = $distintivoDAO;
    }

    public function salvar(Distintivo $distintivo): void
    {
        $this->validarCriacao($distintivo);
        $this->distintivoDAO->salvar($distintivo);
    }

    public function buscarPorId(int $idDistintivo): Distintivo
    {
        if (!$this->distintivoDAO->verificarSeDistintivoExiste($idDistintivo)) {
            throw new RegraDeNegocioException("Distintivo não encontrado!");
        }

        $distintivo = $this->distintivoDAO->buscarPorId($idDistintivo);

        return $distintivo;
    }

    public function listar(): array
    {
        return $this->distintivoDAO->listar();
    }

    public function atualizar(Distintivo $distintivo): void
    {
        $this->validarAtualizacao($distintivo);
        $this->distintivoDAO->atualizar($distintivo);
    }

    public function deletar(int $idDistintivo): void
    {
        if (!$this->distintivoDAO->verificarSeDistintivoExiste($idDistintivo)) {
            throw new RegraDeNegocioException("Distintivo não encontrado!");
        }

        $this->distintivoDAO->deletar($idDistintivo);
    }

    private function validarCriacao(Distintivo $distintivo): void
    {
        if ($distintivo->getIdDistintivo() !== null) {
            throw new RegraDeNegocioException("Distintivo novo não deve possuir ID!");
        }

        if ($this->distintivoDAO->verificarSeTituloExiste($distintivo->getTitulo())) {
            throw new RegraDeNegocioException("Título já utilizado!");
        }

        if ($this->distintivoDAO->verificarSeNomeArquivoExiste($distintivo->getNomeArquivo())) {
            throw new RegraDeNegocioException("Nome do arquivo já utilizado!");
        }
    }

    private function validarAtualizacao(Distintivo $distintivo): void
    {
        if ($distintivo->getIdDistintivo() === null) {
            throw new RegraDeNegocioException("Distintivo deve possuir ID para atualização!");
        }

        if (!$this->distintivoDAO->verificarSeDistintivoExiste($distintivo->getIdDistintivo())) {
            throw new RegraDeNegocioException("Distintivo não encontrado!");
        }

        if ($this->distintivoDAO->verificarTituloParaOutroDistintivo(
            $distintivo->getTitulo(),
            $distintivo->getIdDistintivo()
        )) {
            throw new RegraDeNegocioException("Título já utilizado por outro distintivo!");
        }

        if ($this->distintivoDAO->verificarNomeArquivoParaOutroDistintivo(
            $distintivo->getNomeArquivo(),
            $distintivo->getIdDistintivo()
        )) {
            throw new RegraDeNegocioException("Nome do arquivo já utilizado por outro distintivo!");
        }
    }
}
