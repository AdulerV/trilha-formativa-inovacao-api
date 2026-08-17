<?php
class AlternativaService
{
    private AlternativaDAO $alternativaDAO;

    public function __construct(AlternativaDAO $alternativaDAO)
    {
        $this->alternativaDAO = $alternativaDAO;
    }

    public function salvar(int $idQuestao, Alternativa $alternativa): void
    {
        $this->validarCriacao($idQuestao, $alternativa);

        $alternativa->setIdQuestao($idQuestao);

        $this->alternativaDAO->salvar($alternativa);
    }

    public function buscarPorId(int $idAlternativa, int $idQuestao): Alternativa
    {
        if (!$this->alternativaDAO->verificarSeAlternativaExistePorId($idAlternativa)) {
            throw new RegraDeNegocioException("Alternativa não encontrada!");
        }

        $alternativa = $this->alternativaDAO->buscarPorId($idAlternativa);

        if ($alternativa->getIdQuestao() !== $idQuestao) {
            throw new RegraDeNegocioException("Alternativa não pertence à questão");
        }

        return $alternativa;
    }

    public function listar(): array
    {
        return $this->alternativaDAO->listar();
    }

    public function listarPorQuestao(int $idQuestao): array
    {
        return $this->alternativaDAO->listarPorQuestao($idQuestao);
    }

    public function atualizar(int $idQuestao, Alternativa $alternativa): void
    {
        $this->validarAtualizacao($idQuestao, $alternativa);
        $this->alternativaDAO->atualizar($alternativa, $idQuestao);
    }

    public function deletar(int $idQuestao, int $idAlternativa): void
    {
        if (!$this->alternativaDAO->verificarSeAlternativaExistePorId($idAlternativa)) {
            throw new RegraDeNegocioException("Alternativa não encontrada!");
        }

        $alternativaMapeada = $this->alternativaDAO->buscarPorId($idAlternativa);

        if ($alternativaMapeada->getIdQuestao() !== $idQuestao) {
            throw new RegraDeNegocioException("Alternativa não pertence à questão");
        }

        $this->alternativaDAO->deletar($idAlternativa);
    }

    private function validarCriacao(int $idQuestao, Alternativa $alternativa): void
    {
        if ($alternativa->getIdAlternativa() !== null) {
            throw new RegraDeNegocioException("Alternativa nova não deve possuir ID!");
        }

        if (!$this->alternativaDAO->verificarSeMesmoTipoOutrasAlternativas($idQuestao, $alternativa->getTipoAlternativa())) {
            throw new RegraDeNegocioException("Alternativa não é do mesmo tipo do que as demais!");
        }

        if ($this->alternativaDAO->verificarSeTextoExiste(
            $idQuestao,
            $alternativa->getTexto()
        )) {
            throw new RegraDeNegocioException("Texto já utilizado!");
        }

        if ($alternativa instanceof AlternativaOrdenacao) {
            if ($this->alternativaDAO->verificarSeMesmoNumeroSequenciaOutrasAlternativas($idQuestao, $alternativa->getNumeroSequencia())) {
                throw new RegraDeNegocioException("Já existe uma alternativa com número da sequência {$alternativa->getNumeroSequencia()}");
            }
        }

        if ($alternativa instanceof AlternativaAssociacao) {
            if ($alternativa->getAlternativaAssociada()->getTexto() == $alternativa->getTexto()) {
                throw new RegraDeNegocioException("O texto entre as alternativas não deve ser o mesmo!");
            }

            if ($this->alternativaDAO->verificarTextoParaOutraAlternativa(
                $idQuestao,
                $alternativa->getAlternativaAssociada()->getTexto(),
                $alternativa->getAlternativaAssociada()->getIdAlternativa()
            )) {
                throw new RegraDeNegocioException("Texto de alternativa associada já utilizado por outra alternativa!");
            }
        }
    }

    private function validarAtualizacao(int $idQuestao, Alternativa $alternativa): void
    {
        if ($alternativa->getIdAlternativa() === null) {
            throw new RegraDeNegocioException("Alternativa deve possuir ID para atualização!");
        }

        if (!$this->alternativaDAO->verificarSeAlternativaExistePorId(
            $alternativa->getIdAlternativa()
        )) {
            throw new RegraDeNegocioException("Alternativa não encontrada!");
        }

        if (!$this->alternativaDAO->verificarSeMesmoTipoAlternativa($alternativa->getIdAlternativa(), $alternativa->getTipoAlternativa())) {
            throw new RegraDeNegocioException("Alternativa não é do mesmo tipo!");
        }

        if (!$this->alternativaDAO->verificarSeMesmoTipoOutrasAlternativas($idQuestao, $alternativa->getTipoAlternativa())) {
            throw new RegraDeNegocioException("Alternativa não é do mesmo tipo do que as demais!");
        }

        $alternativaMapeada = $this->alternativaDAO->buscarPorId($alternativa->getIdAlternativa());

        if ($alternativaMapeada->getIdQuestao() !== $idQuestao) {
            throw new RegraDeNegocioException("Alternativa não pertence à questão");
        }

        if ($this->alternativaDAO->verificarTextoParaOutraAlternativa(
            $idQuestao,
            $alternativa->getTexto(),
            $alternativa->getIdAlternativa()
        )) {
            throw new RegraDeNegocioException("Texto já utilizado por outra alternativa!");
        }
    }
}
