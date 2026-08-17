<?php
class TematicaService
{
    private TematicaDAO $tematicaDAO;

    public function __construct(TematicaDAO $tematicaDAO)
    {
        $this->tematicaDAO = $tematicaDAO;
    }

    public function salvar(Tematica $tematica): void
    {
        $this->validarCriacao($tematica);
        $this->tematicaDAO->salvar($tematica);
    }

    public function buscarPorId(int $idTematica): Tematica
    {
        if (!$this->tematicaDAO->verificarSeTematicaExiste($idTematica)) {
            throw new RegraDeNegocioException("Temática não encontrada!");
        }

        $tematica = $this->tematicaDAO->buscarPorId($idTematica);

        return $tematica;
    }

    public function listar(): array
    {
        return $this->tematicaDAO->listar();
    }

    public function atualizar(Tematica $tematica): void
    {
        $this->validarAtualizacao($tematica);
        $this->tematicaDAO->atualizar($tematica);
    }

    public function deletar(int $idTematica): void
    {
        if (!$this->tematicaDAO->verificarSeTematicaExiste($idTematica)) {
            throw new RegraDeNegocioException("Temática não encontrada!");
        }

        $this->tematicaDAO->deletar($idTematica);
    }

    private function validarCriacao(Tematica $tematica): void
    {
        if ($tematica->getIdTematica() !== null) {
            throw new RegraDeNegocioException("Temática novo não deve possuir ID!");
        }

        if ($this->tematicaDAO->verificarSeTituloExiste($tematica->getTitulo())) {
            throw new RegraDeNegocioException("Título já utilizado!");
        }
    }

    private function validarAtualizacao(Tematica $tematica): void
    {
        if ($tematica->getIdTematica() === null) {
            throw new RegraDeNegocioException("Temática deve possuir ID para atualização!");
        }

        if (!$this->tematicaDAO->verificarSeTematicaExiste($tematica->getIdTematica())) {
            throw new RegraDeNegocioException("Temática não encontrada!");
        }

        if ($this->tematicaDAO->verificarTituloParaOutraTematica(
            $tematica->getTitulo(),
            $tematica->getIdTematica()
        )) {
            throw new RegraDeNegocioException("Título já utilizado por outra Temática!");
        }
    }
}
