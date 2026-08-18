<?php
class OcupacaoService
{
    private OcupacaoDAO $ocupacaoDAO;

    public function __construct(OcupacaoDAO $ocupacaoDAO)
    {
        $this->ocupacaoDAO = $ocupacaoDAO;
    }

    public function salvar(Ocupacao $ocupacao): void
    {
        $this->validarCriacao($ocupacao);
        $this->ocupacaoDAO->salvar($ocupacao);
    }

    public function buscarPorId(int $idOcupacao): Ocupacao
    {
        if (!$this->ocupacaoDAO->verificarSeOcupacaoExiste($idOcupacao)) {
            throw new RegraDeNegocioException("Ocupação não encontrada!");
        }

        $ocupacao = $this->ocupacaoDAO->buscarPorId($idOcupacao);

        return $ocupacao;
    }

    public function listar(): array
    {
        return $this->ocupacaoDAO->listar();
    }

    public function atualizar(Ocupacao $ocupacao): void
    {
        $this->validarAtualizacao($ocupacao);
        $this->ocupacaoDAO->atualizar($ocupacao);
    }

    public function deletar(int $idOcupacao): void
    {
        if (!$this->ocupacaoDAO->verificarSeOcupacaoExiste($idOcupacao)) {
            throw new RegraDeNegocioException("Ocupação não encontrada!");
        }

        $this->ocupacaoDAO->deletar($idOcupacao);
    }

    private function validarCriacao(Ocupacao $ocupacao): void
    {
        if ($ocupacao->getIdOcupacao() !== null) {
            throw new RegraDeNegocioException("Ocupação novo não deve possuir ID!");
        }

        if ($this->ocupacaoDAO->verificarSeTituloExiste($ocupacao->getTitulo())) {
            throw new RegraDeNegocioException("Título já utilizado!");
        }
    }

    private function validarAtualizacao(Ocupacao $ocupacao): void
    {
        if ($ocupacao->getIdOcupacao() === null) {
            throw new RegraDeNegocioException("Ocupação deve possuir ID para atualização!");
        }

        if (!$this->ocupacaoDAO->verificarSeOcupacaoExiste($ocupacao->getIdOcupacao())) {
            throw new RegraDeNegocioException("Ocupação não encontrada!");
        }

        if ($this->ocupacaoDAO->verificarTituloParaOutraOcupacao(
            $ocupacao->getTitulo(),
            $ocupacao->getIdOcupacao()
        )) {
            throw new RegraDeNegocioException("Título já utilizado por outra ocupação!");
        }
    }
}
