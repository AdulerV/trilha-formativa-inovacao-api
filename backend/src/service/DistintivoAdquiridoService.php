<?php
class DistintivoAdquiridoService
{
    private DistintivoAdquiridoDAO $distintivoAdquiridoDAO;

    public function __construct(DistintivoAdquiridoDAO $distintivoAdquiridoDAO)
    {
        $this->distintivoAdquiridoDAO = $distintivoAdquiridoDAO;
    }

    public function salvar(DistintivoAdquirido $distintivoAdquirido): void
    {
        $this->validarCriacao($distintivoAdquirido);
        $this->distintivoAdquiridoDAO->salvar($distintivoAdquirido);
    }

    public function buscarPorId(int $idUsuario, int $idDistintivo): DistintivoAdquirido
    {
        if (!$this->distintivoAdquiridoDAO->verificarSeDistintivoAdquiridoExiste($idUsuario, $idDistintivo)) {
            throw new RegraDeNegocioException("Distintivo adquirido não encontrado!");
        }

        $distintivoAdquirido = $this->distintivoAdquiridoDAO->buscarPorId($idUsuario, $idDistintivo);

        return $distintivoAdquirido;
    }

    public function listar(): array
    {
        return $this->distintivoAdquiridoDAO->listar();
    }

    public function listarPorUsuario(int $idUsuario): array
    {
        return $this->distintivoAdquiridoDAO->listarPorUsuario($idUsuario);
    }

/*     public function atualizar(DistintivoAdquirido $distintivoAtual, DistintivoAdquirido $novoDistintivo): void
    {
        $this->validarAtualizacao($distintivoAtual);
        $this->distintivoAdquiridoDAO->atualizar($distintivoAtual, $novoDistintivo);
    }
 */
    public function deletar(int $idUsuario, int $idDistintivo): void
    {
        if (!$this->distintivoAdquiridoDAO->verificarSeDistintivoAdquiridoExiste($idUsuario, $idDistintivo)) {
            throw new RegraDeNegocioException("Distintivo adquirido não encontrado!");
        }

        $this->distintivoAdquiridoDAO->deletar($idUsuario, $idDistintivo);
    }

    private function validarCriacao(DistintivoAdquirido $distintivoAdquirido): void
    {
        if (
            $distintivoAdquirido->getUsuario()->getIdUsuario() === null ||
            $distintivoAdquirido->getDistintivo()->getIdDistintivo() === null
        ) {
            throw new RegraDeNegocioException(
                "Distintivo adquirido deve possuir IDs válidos!"
            );
        }

        if (
            $this->distintivoAdquiridoDAO->verificarSeDistintivoAdquiridoExiste(
                $distintivoAdquirido->getUsuario()->getIdUsuario(),
                $distintivoAdquirido->getDistintivo()->getIdDistintivo()
            )
        ) {
            throw new RegraDeNegocioException(
                "Distintivo adquirido já cadastrado!"
            );
        }
    }

    private function validarAtualizacao(DistintivoAdquirido $distintivoAdquirido): void
    {
        if (
            $distintivoAdquirido->getUsuario()->getIdUsuario() === null ||
            $distintivoAdquirido->getDistintivo()->getIdDistintivo() === null
        ) {
            throw new RegraDeNegocioException(
                "Distintivo adquirido deve possuir IDs válidos!"
            );
        }

        if (
            !$this->distintivoAdquiridoDAO->verificarSeDistintivoAdquiridoExiste(
                $distintivoAdquirido->getUsuario()->getIdUsuario(),
                $distintivoAdquirido->getDistintivo()->getIdDistintivo()
            )
        ) {
            throw new RegraDeNegocioException(
                "Distintivo adquirido não encontrado!"
            );
        }
    }
}
