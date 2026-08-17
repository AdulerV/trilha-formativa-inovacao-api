<?php
class ProgressoMissaoService
{
    private ProgressoMissaoDAO $progressoMissaoDAO;

    public function __construct(ProgressoMissaoDAO $progressoMissaoDAO)
    {
        $this->progressoMissaoDAO = $progressoMissaoDAO;
    }

    public function salvar(ProgressoMissao $progressoMissao): void
    {
        $this->validarCriacao($progressoMissao);
        $this->progressoMissaoDAO->salvar($progressoMissao);
    }

    public function listar(): array
    {
        return $this->progressoMissaoDAO->listar();
    }

    public function listarPorUsuario(int $idUsuario): array
    {
        return $this->progressoMissaoDAO->listarPorUsuario($idUsuario);
    }

    public function buscarPorId(int $idUsuario, int $idMissao): ProgressoMissao
    {
        if (!$this->progressoMissaoDAO->verificarSeProgressoMissaoExiste($idUsuario, $idMissao)) {
            throw new RegraDeNegocioException("Progresso de missão não encontrado!");
        }

        $progressoMissao = $this->progressoMissaoDAO->buscarPorId($idUsuario, $idMissao);

        return $progressoMissao;
    }
    
    public function atualizar(ProgressoMissao $progressoMissao): void
    {
        $this->validarAtualizacao($progressoMissao);
        $this->progressoMissaoDAO->atualizar($progressoMissao);
    }

    public function deletar(int $idUsuario, int $idMissao): void
    {
        if (!$this->progressoMissaoDAO->verificarSeProgressoMissaoExiste($idUsuario, $idMissao)) {
            throw new RegraDeNegocioException("Progresso de missão não encontrado!");
        }

        $this->progressoMissaoDAO->deletar($idUsuario, $idMissao);
    }

    private function validarCriacao(ProgressoMissao $progressoMissao): void
    {
        if (
            $progressoMissao->getUsuario()->getIdUsuario() === null ||
            $progressoMissao->getMissao()->getIdMissao() === null
        ) {
            throw new RegraDeNegocioException(
                "Progresso de missão deve possuir IDs válidos!"
            );
        }

        if (
            $this->progressoMissaoDAO->verificarSeProgressoMissaoExiste(
                $progressoMissao->getUsuario()->getIdUsuario(),
                $progressoMissao->getMissao()->getIdMissao()
            )
        ) {
            throw new RegraDeNegocioException(
                "Progresso de missão já cadastrado!"
            );
        }
    }

    private function validarAtualizacao(ProgressoMissao $progressoMissao): void
    {
        if (
            $progressoMissao->getUsuario()->getIdUsuario() === null ||
            $progressoMissao->getMissao()->getIdMissao() === null
        ) {
            throw new RegraDeNegocioException(
                "Progresso de missão deve possuir IDs válidos!"
            );
        }

        if (
            !$this->progressoMissaoDAO->verificarSeProgressoMissaoExiste(
                $progressoMissao->getUsuario()->getIdUsuario(),
                $progressoMissao->getMissao()->getIdMissao()
            )
        ) {
            throw new RegraDeNegocioException(
                "Progresso de missão não encontrado!"
            );
        }
    }
}
