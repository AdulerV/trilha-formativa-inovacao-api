<?php
class QuestaoService
{
    private QuestaoDAO $questaoDAO;
    private ?MissaoDAO $missaoDAO;
    private AlternativaService $alternativaService;

    public function __construct(
        QuestaoDAO $questaoDAO,
        ?MissaoDAO $missaoDAO,
        AlternativaService $alternativaService
    ) {
        $this->questaoDAO = $questaoDAO;
        $this->missaoDAO = $missaoDAO;
        $this->alternativaService = $alternativaService;
    }

    public function salvar(int $idMissao, Questao $questao): void
    {
        $this->validarCriacao($idMissao, $questao);
        $this->validarMissao($idMissao);

        $this->questaoDAO->salvar($idMissao, $questao);

        foreach ($questao->getAlternativas() as $alternativa) {
            $this->alternativaService->salvar(
                $questao->getIdQuestao(),
                $alternativa
            );
        }
    }

    public function buscarPorId(int $idQuestao, int $idMissao): Questao
    {
        if (!$this->questaoDAO->verificarSeQuestaoExistePorId($idQuestao)) {
            throw new RegraDeNegocioException("Questão não encontrada!");
        }

        $questao = $this->questaoDAO->buscarPorId($idQuestao);

        if ($questao->getIdMissao() !== $idMissao) {
            throw new RegraDeNegocioException("Questão não pertence à missão");
        }

        return $this->questaoDAO->buscarPorId($idQuestao);
    }

    public function buscarSomentePorId(int $idQuestao): Questao
    {
        if (!$this->questaoDAO->verificarSeQuestaoExistePorId($idQuestao)) {
            throw new RegraDeNegocioException("Questão não encontrada!");
        }

        return $this->questaoDAO->buscarPorId($idQuestao);
    }

    public function listar(): array
    {
        return $this->questaoDAO->listar();
    }


    public function listarPorMissao(int $idMissao): array
    {
        $this->validarMissao($idMissao);

        return $this->questaoDAO->listarPorMissao($idMissao);
    }

    public function atualizar(Questao $questao): void
    {
        $this->validarAtualizacao($questao);

        $this->questaoDAO->atualizar($questao);

        foreach ($questao->getAlternativas() as $alternativa) {
            if ($alternativa->getIdAlternativa() === null) {
                $this->alternativaService->salvar($questao->getIdQuestao(), $alternativa);
            } else {
                $this->alternativaService->atualizar($questao->getIdQuestao(), $alternativa);
            }
        }
    }

    public function deletar(int $idMissao, int $idQuestao): void
    {
        if (!$this->questaoDAO->verificarSeQuestaoExistePorId($idQuestao)) {
            throw new RegraDeNegocioException("Questão não encontrada!");
        }

        $questaoMapeada = $this->questaoDAO->buscarPorId($idQuestao);

        if ($questaoMapeada->getIdMissao() !== $idMissao) {
            throw new RegraDeNegocioException("Questão não pertence à missão");
        }

        $this->questaoDAO->deletar($idQuestao);
    }

    private function validarMissao(int $idMissao): void
    {
        if (!$this->missaoDAO->verificarSeMissaoExiste($idMissao)) {
            throw new RegraDeNegocioException("Missão não encontrada!");
        }

        $tipo = $this->missaoDAO->buscarTipoMissao($idMissao);

        if ($tipo !== Missao::TIPO_ATIVIDADE) {
            throw new RegraDeNegocioException(
                "Questões só podem ser vinculadas a missões do tipo atividade!"
            );
        }
    }

    private function validarCriacao(int $idMissao, Questao $questao): void
    {
        if ($questao->getIdQuestao() !== null) {
            throw new RegraDeNegocioException("Questão nova não deve possuir ID!");
        }

        if ($this->questaoDAO->verificarSeEnunciadoExiste(
            $idMissao,
            $questao->getEnunciado()
        )) {
            throw new RegraDeNegocioException("Enunciado já utilizado!");
        }
    }

    private function validarAtualizacao(Questao $questao): void
    {
        if ($questao->getIdQuestao() === null) {
            throw new RegraDeNegocioException("Questão deve possuir ID para atualização!");
        }

        if (!$this->questaoDAO->verificarSeQuestaoExistePorId(
            $questao->getIdQuestao()
        )) {
            throw new RegraDeNegocioException("Questão não encontrada!");
        }

        if ($this->questaoDAO->verificarSeQuestaoExisteParaOutraMissao($questao->getIdQuestao(), $questao->getIdMissao())) {
            throw new RegraDeNegocioException("Questão não pertente à missão");
        }

        if ($this->questaoDAO->verificarEnunciadoParaOutraQuestao(
            $questao->getEnunciado(),
            $questao->getIdQuestao()
        )) {
            throw new RegraDeNegocioException(
                "Enunciado já utilizado por outra questão!"
            );
        }
    }
}
