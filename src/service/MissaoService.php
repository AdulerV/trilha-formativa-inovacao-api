<?php
class MissaoService
{
    private MissaoDAO $missaoDAO;
    private QuestaoService $questaoService;


    public function __construct(MissaoDAO $missaoDAO, QuestaoService $questaoService)
    {
        $this->missaoDAO = $missaoDAO;
        $this->questaoService = $questaoService;
    }

    public function salvar(Missao $missao): void
    {
        $this->validarCriacao($missao);
        $this->missaoDAO->salvar($missao);

        if ($missao instanceof MissaoAtividade) {
            foreach ($missao->getQuestoes() as $questao) {
                $this->questaoService->salvar($questao->getIdMissao(), $questao);
            }
        }
    }

    public function listar(): array
    {
        return $this->missaoDAO->listar();
    }

    public function buscarPorId(int $idMissao): Missao
    {
        if (!$this->missaoDAO->verificarSeMissaoExiste($idMissao)) {
            throw new RegraDeNegocioException("Missão não encontrada!");
        }

        $missao = $this->missaoDAO->buscarPorId($idMissao);

        return $missao;
    }

    public function atualizar(Missao $missao): void
    {
        $this->validarAtualizacao($missao);
        $this->missaoDAO->atualizar($missao);

        if ($missao instanceof MissaoAtividade) {
            foreach ($missao->getQuestoes() as $questao) {
                if ($questao->getIdQuestao() === null) {
                    $this->questaoService->salvar($questao->getIdMissao(), $questao);
                } else {
                    $this->questaoService->atualizar($questao);
                }
            }
        }
    }

    public function deletar(int $idMissao): void
    {
        if (!$this->missaoDAO->verificarSeMissaoExiste($idMissao)) {
            throw new RegraDeNegocioException("Missão não encontrada!");
        }

        $this->missaoDAO->deletar($idMissao);
    }

    private function validarCriacao(Missao $missao): void
    {
        if ($missao->getIdMissao() !== null) {
            throw new RegraDeNegocioException("Missão nova não deve possuir ID!");
        }

        if ($this->missaoDAO->verificarSeTituloExiste($missao->getTitulo())) {
            throw new RegraDeNegocioException("Título já utilizado!");
        }
    }

    private function validarAtualizacao(Missao $missao): void
    {
        if ($missao->getIdMissao() === null) {
            throw new RegraDeNegocioException("Missão deve possuir ID para atualização!");
        }

        if (!$this->missaoDAO->verificarSeMissaoExiste($missao->getIdMissao())) {
            throw new RegraDeNegocioException("Missão não encontrada!");
        }

        if ($this->missaoDAO->verificarTituloParaOutraMissao(
            $missao->getTitulo(),
            $missao->getIdMissao()
        )) {
            throw new RegraDeNegocioException("Título já utilizado por outra missão!");
        }

        $this->validarDistintivoMantido($missao);
    }

    /**
     * O distintivo de uma tarefa é definido no cadastro e não muda
     * mais: aventureiros que já concluíram a tarefa receberam aquele
     * distintivo, e trocá-lo deixaria o histórico apontando para uma
     * conquista que a tarefa não concede mais.
     */
    private function validarDistintivoMantido(Missao $missao): void
    {
        if (!$missao instanceof MissaoAtividadeTarefa) {
            return;
        }

        $missaoAtual = $this->missaoDAO->buscarPorId((int) $missao->getIdMissao());

        if (!$missaoAtual instanceof MissaoAtividadeTarefa) {
            return;
        }

        if ($missaoAtual->getDistintivo()->getIdDistintivo()
            !== $missao->getDistintivo()->getIdDistintivo()
        ) {
            throw new RegraDeNegocioException(
                "O distintivo da tarefa não pode ser alterado após o cadastro!"
            );
        }
    }
}
