<?php

declare(strict_types=1);

class AlternativaMarcadaService
{
    private AlternativaMarcadaDAO $alternativaMarcadaDAO;
    private AlternativaDAO $alternativaDAO;

    public function __construct(
        AlternativaMarcadaDAO $alternativaMarcadaDAO,
        AlternativaDAO $alternativaDAO
    ) {
        $this->alternativaMarcadaDAO = $alternativaMarcadaDAO;
        $this->alternativaDAO = $alternativaDAO;
    }

    public function salvar(AlternativaMarcada $alternativaMarcada): void
    {
        $this->validarCriacao($alternativaMarcada);

        $idAlternativa = $alternativaMarcada->getAlternativa()->getIdAlternativa();
        $alternativaBanco = $this->alternativaDAO->buscarPorId($idAlternativa);

        if (!$alternativaBanco) {
            throw new RegraDeNegocioException("A alternativa associada não foi encontrada no sistema!");
        }

        $estaCorreta = false;

        if ($alternativaBanco instanceof AlternativaMultiplaEscolha) {
            $estaCorreta = $alternativaBanco->isCorreta();
        } elseif ($alternativaBanco instanceof AlternativaOrdenacao) {
            $estaCorreta = $this->processarValidacaoOrdenacao($alternativaMarcada, $alternativaBanco);
        } elseif ($alternativaBanco instanceof AlternativaAssociacao) {
            $estaCorreta = $this->processarValidacaoAssociacao($alternativaMarcada, $alternativaBanco);
        } else {
            throw new RegraDeNegocioException("Tipo de alternativa desconhecido para validação.");
        }

        $alternativaMarcada->setCorreta($estaCorreta);
        $this->alternativaMarcadaDAO->salvar($alternativaMarcada);
    }

    private function processarValidacaoOrdenacao(AlternativaMarcada $marcada, AlternativaOrdenacao $banco): bool
    {
        if ($marcada->getSequenciaRespondida() === null) {
            return false;
        }

        return $marcada->getSequenciaRespondida() === $banco->getNumeroSequencia();
    }

    private function processarValidacaoAssociacao(AlternativaMarcada $marcada, AlternativaAssociacao $banco): bool
    {
        $associadaBanco = $banco->getAlternativaAssociada();

        if ($associadaBanco === null || $marcada->getIdAlternativaAssociadaRespondida() === null) {
            return false;
        }

        return $marcada->getIdAlternativaAssociadaRespondida() === $associadaBanco->getIdAlternativa();
    }

    public function listar(): array
    {
        return $this->alternativaMarcadaDAO->listar();
    }

    public function listarPorUsuario(int $idUsuario): array
    {
        return $this->alternativaMarcadaDAO->listarPorUsuario($idUsuario);
    }

    public function buscarPorId(int $idUsuario, int $idAlternativa): AlternativaMarcada
    {
        if (!$this->alternativaMarcadaDAO->verificarSeAlternativaMarcadaExiste($idUsuario, $idAlternativa)) {
            throw new RegraDeNegocioException("Alternativa marcada não encontrada!");
        }

        return $this->alternativaMarcadaDAO->buscarPorId($idUsuario, $idAlternativa);
    }

    public function atualizar(int $idUsuario, int $idAlternativa, AlternativaMarcada $novaMarcacao): void
    {
        if (!$this->alternativaMarcadaDAO->verificarSeAlternativaMarcadaExiste($idUsuario, $idAlternativa)) {
            throw new RegraDeNegocioException("Não é possível atualizar uma marcação inexistente!");
        }

        $alternativaBanco = $this->alternativaDAO->buscarPorId($idAlternativa);
        if (!$alternativaBanco) {
            throw new RegraDeNegocioException("A alternativa associada não foi encontrada no sistema!");
        }
        $estaCorreta = false;

        if ($alternativaBanco instanceof AlternativaMultiplaEscolha) {
            $estaCorreta = $alternativaBanco->isCorreta();
        } elseif ($alternativaBanco instanceof AlternativaOrdenacao) {
            $estaCorreta = $this->processarValidacaoOrdenacao($novaMarcacao, $alternativaBanco);
        } elseif ($alternativaBanco instanceof AlternativaAssociacao) {
            $estaCorreta = $this->processarValidacaoAssociacao($novaMarcacao, $alternativaBanco);
        } else {
            throw new RegraDeNegocioException("Tipo de alternativa desconhecido para validação.");
        }

        $novaMarcacao->setCorreta($estaCorreta);
        $novaMarcacao->getUsuario()->setIdUsuario($idUsuario);
        $novaMarcacao->getAlternativa()->setIdAlternativa($idAlternativa);

        $this->alternativaMarcadaDAO->atualizar($novaMarcacao);
    }

    public function deletar(int $idUsuario, int $idAlternativa): void
    {
        if (!$this->alternativaMarcadaDAO->verificarSeAlternativaMarcadaExiste($idUsuario, $idAlternativa)) {
            throw new RegraDeNegocioException("Alternativa marcada não encontrada!");
        }

        $this->alternativaMarcadaDAO->deletar($idUsuario, $idAlternativa);
    }

    /**
     * A marcação é idempotente por (usuário, alternativa).
     *
     * A regra anterior recusava a segunda marcação da mesma
     * alternativa. Como o sistema permite refazer quiz e tarefa, essa
     * recusa impedia justamente a nova tentativa de ser salva: a
     * marcação repetida é o comportamento esperado, e o DAO substitui
     * a resposta anterior.
     *
     * As regras de tentativas e pontuação continuam onde sempre
     * estiveram, na conclusão da missão.
     */
    private function validarCriacao(AlternativaMarcada $alternativaMarcada): void
    {
        if (
            $alternativaMarcada->getUsuario()->getIdUsuario() === null ||
            $alternativaMarcada->getAlternativa()->getIdAlternativa() === null
        ) {
            throw new RegraDeNegocioException(
                "Alternativa marcada deve possuir IDs de usuário e alternativa válidos!"
            );
        }
    }
}
